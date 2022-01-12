<?php

require 'vendor/autoload.php';
use Aws\S3\S3Client;

class S3Thumber {
    private $aws_access_key_id;
    private $aws_secret_access_key;
    private $region_name;
    
    private $s3;
/* 初始化S3接口 */
    public function S3Thumber($aws_access_key_id, $aws_secret_access_key, $region_name) {
        $this->aws_access_key_id = $aws_access_key_id;
        $this->aws_secret_access_key = $aws_secret_access_key;
        $this->region_name = $region_name;
        
        $this->s3 = S3Client::factory([
            'version' => 'latest',
            'region' => $this->region_name,
            'credentials' => [
                'key' => $this->aws_access_key_id,
                'secret' => $this->aws_secret_access_key
            ]
        ]);
    }
/* 解析URL，得到缩略图参数，例如/thumb_180_182 表示w180 h182 居中 保持纵横比 */
    private function parse_thumb($key) {
        $key_result = $key;

        if (strpos($key, '/thumb_') === false) {
            return null;
        } else {
            if (strlen($key) > 0) {
                if (substr($key, -1, 1) === '/') {
                    $key_result = substr($key, 0, -1);
                }

                if (substr($key, -4, 4) === '.jpg') {
                    $key_result = substr($key, 0, -4);
                }
            }

            $seg = explode('/thumb_', $key_result);

            if (sizeof($seg) !== 2) {
                return null;
            } else {
                $parts = explode('_', $seg[1]);

                if (sizeof($parts) !== 2) {
                    return null;
                } else {
                    $w = (int) $parts[0];
                    $h = (int) $parts[1];

                    return [$w, $h, $seg[0]];
                }
            }
        }
    }
/* 解析URL，剪裁掉部分，格式 imageurl/crop_x_y_w_h */
    private function parse_crop($key) {
        $key_result = $key;

        if (strpos($key, '/crop_') === false) {
            return null;
        } else {
            if (strlen($key) > 0 && substr($key, -1, 1) === '/') {
                $key_result = substr($key, 0, -1);
            }

            $seg = explode('/crop_', $key_result);

            if (sizeof($seg) !== 2) {
                return null;
            } else {
                $parts = explode('_', $seg[1]);

                if (sizeof($parts) !== 4) {
                    return null;
                } else {
                    $x = (int) $parts[0];
                    $y = (int) $parts[1];
                    $w = (int) $parts[2];
                    $h = (int) $parts[3];

                    return [$x, $y, $w, $h, $seg[0]];
                }
            }
        }
    }
/* 获取待处理图片的地址 下载 */
    private function from_s3($bucket, $key) {
        try {
            $res = $this->s3->getObject([
                'Bucket' => $bucket,
                'Key' => $key
            ]);
    
            return imagecreatefromstring($res['Body']);
        } catch (Exception $e) {
            echo 'error'.$e;
            return null;
        }
    }
/* 处理完图片存储的地址，上传 */
    private function to_s3($img, $bucket, $key, $acl = 'public-read') {
        $fp = fopen("php://memory", 'rw+');
        imagejpeg($img, $fp);
        fseek($fp,0);
        $buffer = stream_get_contents($fp);
        fclose($fp);
        imagedestroy($img);
        try {
            $this->s3->putObject([
                'Bucket' => $bucket,
                'Key' => $key,
                'ACL' => $acl,
                'ContentType' => 'image/jpeg',
                'Body' => $buffer
            ]);
    
            return $buffer;
        } catch (Exception $e) {
            echo 'error'.$e;
            return null;
        }
    }
/* 取缩略图入口，取图片，计算横纵比 、缩小，上传 */
    private function resizeit($bucket, $key, $params) {
        [$w, $h, $src_key] = $params;
        $img = $this->from_s3($bucket, $src_key);
        if (is_null($img)) {
            echo 'error'.$e;
            return null;
        }

        $w0 = imagesx($img);
        $h0 = imagesy($img);

        $r = min($w0 / $w, $h0 / $h);

        $wresized = ceil($w0 / $r);
        $hresized = ceil($h0 / $r);

        $img_resized = imagescale($img, $wresized, $hresized);
        $img_cropped = imagecrop($img_resized, [
            'x' => (int) (floor($wresized - $w) / 2),
            'y' => (int) (floor($hresized - $h) / 2),
            'width' => (int) ($w),
            'height' => (int) ($h)
        ]);

        try {
            $buffer = $this->to_s3($img_cropped, $bucket, $key);
        } catch (Excetion $e) {
            echo 'error'.$e;
            return null;
        }

        return [$key, $img_cropped];
    }
/* 取缩略图入口，取图片，计算剪裁区域， 剪裁、上传 */
    private function cropit($bucket, $key, $params) {
        [$x, $y, $w, $h, $src_key] = $params;
        
        try {
            $img = $this->from_s3($bucket, $src_key);
        } catch (Exception $e) {
            echo 'error'.$e;
            return null;
        }

        $w0 = imagesx($img);
        $h0 = imagesy($img);

        $img_cropped = imagecrop($img, [
            'x' => (int) (max($x, 0)),
            'y' => (int) (max($y, 0)),
            'width' => (int) (min($w0, $x + $w)),
            'height' => (int) (min($h0, $y + $h))
        ]);

        try {
            $buffer = $this->to_s3($img_cropped, $bucket, $key);
        } catch (Excetion $e) {
            echo 'error'.$e;
            return null;
        }
        return [$key, $buffer];
    }
/* 主入口 负责拆分参数 */
    public function justDoit($bucket, $key) {
        $thumb_params = $this->parse_thumb($key);

        if (!is_null($thumb_params)) {
            return $this->resizeit($bucket, $key, $thumb_params);
        } else {
            $crop_params = $this->parse_crop($key);

            if (!is_null($crop_params)) {
                return $this->cropit($bucket, $key, $crop_params);
            }
        }
    }
}
