Login plugin
Create a plugin that will have the following functionality:
1) To be developed as wordpress plugin with option to activate/deactivate
2) Once activated the plugin should appear in the left admin menu:
3) Clicking on this link, should open empty page with only instructions how to use the plugin
4) Within this plugin logic you have to develop a shortcode [login_form] that can be added on any page content like in this example:
5) Page that has this type of shortcode, on public needs to render a simple login form, something like this example:
6) On login you should send data to 3rd party login service, that will happen on server side.
*Login should be done on server side by using WP API functionality.
*This is external API service that needs to be called with CURL, here is example URL:
Request URL: http://demo1.btobet.net/wp-json/btobet/v1/login/?
Request Method: POST
Content-Type: application/json; charset=UTF-8
Post body object:
{
    "username": "usernameinput",
    "password": "passwordinput"
}
7) Bonus task, block the login form that you have created in previous tasks, if the user tries to login more than 3 times from same IP address within 30 minutes timeframe.
