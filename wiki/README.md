wiki目录说明
==============
这个目录是wechat-php-sdk项目的wiki文档
Make By：binsee

##说明 
**这里的wiki文档可以让你更好的了解`wechat-php-sdk`项目，更好的使用。 **

**欢迎对wiki文档内容进行补充，把`wechat-php-sdk`项目变得更清晰易懂。**

##为你的github生成wiki
**如果你在github上fork了`wechat-php-sdk`项目，而且想为项目生成wiki，可以用这里的文件来生成。**


###使用步骤：
1. 在你的github上，fork或者创建`wechat-php-sdk`项目

2. 激活项目wiki，已激活的请跳过
```
进入项目的设置页面：
https://github.com/你的用户名/wechat-php-sdk/settings
找到Features一栏，把 Wikis 选项打钩，就可以激活你项目的wiki了
```

3. 进入项目的wiki页面：
`https://github.com/你的用户名/wechat-php-sdk/wiki`

4. 点绿色的 `Create the first page` 按钮

5. 直接到下方点 `Save Page` 按钮

6. 在右边找到 `Clone this wiki locally` 一栏，复制git地址：
`git@github.com:你的用户名/wechat-php-sdk.wiki.git`

7. 在项目的上一层目录执行 
`git clone git@github.com:你的用户名/wechat-php-sdk.wiki.git`

8. 进入新出现的 `wechat-php-sdk.wiki` 目录，把wiki目录下的文件都复制过来
> **这里有个高级用法，就是使用连接方式把wiki目录链接过来，而不是复制**
> windows下的用法:
```
#如项目目录为：E:\wechat-php-sdk\
#项目wiki目录为:E:\wechat-php-sdk.wiki\
执行命令：
mklink /j E:\wechat-php-sdk.wiki\wiki E:\wechat-php-sdk\wiki
```
> 这样的话，两个目录就会被联接到一起。
> 以后进行更改wiki在哪个目录都行，另一个目录都是同步的。
> 分别在 项目目录 和 项目wiki目录 进行git提交就可以了。

9. 然后直接缓存上传即可。

10. 现在去你github上项目的wiki目录里看一下吧


##生成page
**你也可以将wiki文档，生成为个人站点，会更加直观。**

**比如使用 Hexo 或者其他的框架之类。**

**这块的话，请自行搜索相关资料。**