# Picture Automated Resize On Graviton2

利用CloudFront和EC2的Graviton2实例，可以实现对S3上存放的图片自动改变尺寸。

## 免责声明

建议测试过程中使用此方案，生产环境使用请自行考虑评估。

当您对方案需要进一步的沟通和反馈后，可以联系 nwcd_labs@nwcdcloud.cn 获得更进一步的支持。

欢迎联系参与方案共建和提交方案需求, 也欢迎在 github 项目 issue 中留言反馈 bugs。

## 项目说明

此方案作为[Serverless Image Handler](https://aws.amazon.com/solutions/implementations/serverless-image-handler/)的补充，所实现的功能与Serverless Image Handler完全一致，但此方案利用了新发布的Graviton2机型，在拥有海量工作负载且变化平缓的前提下，可以达到对成本大幅度优化，并且可以突破Lambda限制，处理大尺寸图片。


## 使用方式

参见blog:

https://aws.amazon.com/cn/blogs/china/gravity2-and-cloudfront-generate-thumbnails-for-s3-object-storage/


