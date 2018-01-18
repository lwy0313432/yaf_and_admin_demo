#  后台代码需要安装nodejs
## Node 安装办法：

``` bash

wget 'https://nodejs.org/dist/v6.11.3/node-v6.11.3-linux-x64.tar.xz'

xz -d  node-v6.11.3-linux-x64.tar.xz

tar xvf node-v6.11.3-linux-x64.tar

#进入root
su

apt-get install npm

vi /etc/profile

#最后一行加入PATH
export PATH=$PATH:/home/www/node-v6.11.3-linux-x64/bin

source /etc/profile

#退出root
su - www

git clone ssh://git@118.190.126.206:1022/back-end/fe-dev.git

cd fe_fangwei/management_sys_dev/

su

npm install -g cnpm --registry=https://registry.npm.taobao.org

su - www

cnpm install

配置 management_sys_dev/src/config.js 和 management_sys_dev/build.sh

./build.sh

```

