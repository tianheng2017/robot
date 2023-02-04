## OKEX模拟盘简易单用户/单币种/单仓位量化机器人

### 使用方法：

#### 1、环境配置
```
安装宝塔
同步服务器时间为北京时间（重要）
安装php7.4（安装redis扩展）
安装redis7（设置密码123654后重启redis服务）
安装supervisor
uniapp打包h5后放到public目录
```

#### 2、supervisor添加以下4个进程
```
sudo php server.php start
sudo php client.php start
sudo php account.php start
sudo php api.php start
```

#### 3、网站设置->伪静态，粘贴以下内容
```
location /wss {
    proxy_redirect off;
    proxy_pass http://127.0.0.1:2000;
    proxy_set_header Host $host;
    proxy_set_header X-Real_IP $remote_addr;
    proxy_set_header X-Forwarded-For $remote_addr:$remote_port;
    proxy_http_version 1.1;
    proxy_set_header Upgrade $http_upgrade;
    proxy_set_header Connection upgrade;
}
```