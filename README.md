# Picture Automated Resize On Graviton2

利用CloudFront和EC2的Graviton2实例，可以实现对S3上存放的图片自动改变尺寸。

## 免责声明

建议测试过程中使用此方案，生产环境使用请自行考虑评估。

当您对方案需要进一步的沟通和反馈后，可以联系 nwcd_labs@nwcdcloud.cn 获得更进一步的支持。

欢迎联系参与方案共建和提交方案需求, 也欢迎在 github 项目 issue 中留言反馈 bugs。

## 项目说明

本项目让用户可以使用 Elasticsearch 来分析 VPC Flow Logs。这个项目提供日志进入 Elasticsearch 的框架，用户可以基于日志数据进行分析和可视化。

在 CloudWatch Logs Insights 未进入 AWS 大陆区域之前，此方法可以帮助用户快速分析和可视化 VPC Flow Logs，识别流量模式和潜在风险。

![](images/vpc-flow-logs.png)

## 使用方式

### 准备工作


部署成功后，可在 CloudFormation 中看到已经部署的 Stack。实验完成后，记得删除 Stack，避免持续产生费用。

## FAQ

**问：这个演示有什么用？**

答：这个演示主要目的是展示 CDK 的完整使用流程。此外，在中国区没有 CloudWatch Logs Insights 的时候可以作为 Flow Logs 的分析和可视化基础。


