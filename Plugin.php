<?php
/**
 * 禁止中国大陆IP访问
 * 被禁止的IP会显示一个提示
 * @package ProhibitIP
 * @author culturesun
 * @version 1.2
 * @update: 2022.11.30
 * @link https://culturesun.site
 */
class ProhibitIP_Plugin implements Typecho_Plugin_Interface
{
    public static function activate()
    {
        Typecho_Plugin::factory('index.php')->begin = array('ProhibitIP_Plugin', 'ProhibitIP');
        Typecho_Plugin::factory('admin/common.php')->begin = array('ProhibitIP_Plugin', 'ProhibitIP');
        return "启用ProhibitIP成功";
    }

    public static function deactivate()
    {
        return "禁用ProhibitIP成功";
    }

    public static function config(Typecho_Widget_Helper_Form $form)
    {
        // Cloudflare support
        $cloudflare_support = new Typecho_Widget_Helper_Form_Element_Checkbox('cloudflare_support', array('enable' => _t('启用Cloudflare支持')), null, _t('Cloudflare支持'), _t('如果你的网站使用了Cloudflare，启用这个选项可以确保插件能正确获取用户的IP地址。'));
        $form->addInput($cloudflare_support);
    }

    public static function personalConfig(Typecho_Widget_Helper_Form $form)
    {}

    public static function prohibitIP()
    {
        if (ProhibitIP_Plugin::checkIP()) {
            header('HTTP/1.1 403 Forbidden');
            echo '<!DOCTYPE html>
            <html>
            <head>
                <title>Access Denied</title>
                <style>
                    body {
                        font-family: Arial, sans-serif;
                        margin: 0;
                        padding: 0;
                        background-color: #f0f0f0;
                        display: flex;
                        align-items: center;
                        justify-content: center;
                        height: 100vh;
                    }
                    .container {
                        text-align: center;
                    }
                    h1 {
                        color: #333;
                    }
                    p {
                        color: #666;
                    }
                    img {
                        width: 100px;
                        height: 100px;
                    }
                    @media (max-width: 600px) {
                        h1 {
                            font-size: 1.5em;
                        }
                        p {
                            font-size: 1.2em;
                        }
                        img {
                            width: 50px;
                            height: 50px;
                        }
                    }
                </style>
            </head>
            <body>
                <div class="container">
                    <img src="#########自己写你自己的" alt="Access Denied">
                    <h1>你的访问被拒止。</h1>
                    <p>本站不提供给局域网居民服务，但是欢迎你越过长城，走向世界。</p>
                </div>
            </body>
            </html>';
            exit;
        }
    }

   private static function checkIP()
{
    $request = new Typecho_Request;
    $ip = trim($request->getIp());
    
    $config = Typecho_Widget::widget('Widget_Options')->plugin('ProhibitIP');
    if (isset($config->cloudflare_support) && in_array('enable', $config->cloudflare_support)) {
        // Check for Cloudflare headers and use them to get the real IP
        if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
            $ip = $_SERVER['HTTP_CF_CONNECTING_IP'];
        } elseif (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
    }

    $login_addr_arra = file_get_contents('http://ip-api.com/json/'.$ip.'?lang=en');
    $login_addr_arra = json_decode($login_addr_arra,true);
    $countryCode = $login_addr_arra['countryCode'];
    // 判断是否为大陆IP
    return $countryCode == 'CN';
}
}
