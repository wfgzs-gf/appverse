=== Appverse ===
Contributors: appverse
Requires at least: 6.4
Tested up to: 7.1
Requires PHP: 8.1
Stable tag: 1.0.0
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A mobile app-store style WordPress theme with apps, charts, categories, a community feed, daily check-in and a profile center.

== Description ==

Appverse 是一款移动应用商店风格的 WordPress 主题。它把应用商店的常见结构做成了可编辑的 WordPress 内容：

* 应用（自定义文章类型）与分类、标签、应用集
* 首页轮播、快捷入口、推荐应用、应用列表与应用管理
* 应用排行榜、分类页、独立开发专区
* 社区动态（使用原生文章），支持关联应用与「动态 / 附件 / 开发者」筛选
* 每日签到、积分、等级进度与个人中心
* 下载弹窗、点赞 / 点踩、AJAX 签到
* 移动优先，桌面端居中为手机应用外壳
* 首次启用自动导入一套完整演示内容，可在「外观 → Appverse 演示内容」中移除

== Installation ==

1. 在 WordPress 后台进入「外观 → 主题 → 安装主题」，上传 `appverse.zip` 并启用。
2. 首次启用会创建演示应用、动态、页面、菜单与轮播图。
3. 进入「设置 → 固定链接」保存一次，刷新伪静态规则。
4. 在「外观 → 自定义 → Appverse 主题设置」中修改轮播、通知角标与页脚信息。
5. 在「应用商店 → 上架应用」中维护真实应用，替换演示数据。

== Frequently Asked Questions ==

= 演示内容会自动创建吗？ =
会。主题首次启用时，如果还没有应用数据，会自动导入演示内容。你也可以在「外观 → Appverse 演示内容」中随时移除或重新导入。

= 如何替换应用图标？ =
编辑应用，在右侧「文章缩略图」上传 256×256 的图标；应用详情页截图在「应用信息 → 应用截图」中选择。

= 如何修改首页轮播？ =
「外观 → 自定义 → Appverse 主题设置 → 首页轮播」，三张轮播均可替换图片、标题、描述、按钮与链接。未设置时使用主题内置演示素材。

= 下载按钮会下载真实安装包吗？ =
演示数据的下载地址指向站内下载管理页。请编辑应用，在「应用信息 → 下载地址」中填入真实的 APK 或下载页地址。

= 个人中心的等级和资产在哪里修改？ =
在「用户 → 编辑用户」的「Appverse 应用商店资料」中修改等级、经验、积分、分享值与流量。

== Credits ==

* 主题界面与演示数据均为本项目原创。
* 演示应用图标、轮播图、头像与截图由主题内置生成，采用 CC0 授权，可自由替换。
* 图标为内联 SVG，无第三方图标库依赖。

== Changelog ==

= 1.0.0 =
* 首个版本：应用商店首页、排行、分类、独立开发、广场、签到与个人中心。
