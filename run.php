<?php error_reporting(E_ALL);

require __DIR__ . "/vendor/autoload.php";

use League\CLImate\CLImate;

(function($climate) {
  system(strtolower(substr(php_uname("s"), 0, 3)) == "lin" ? "clear" : "cls");

  function getRandomEmail(int $length, string $domain = "gmail.com") {
    return substr(md5(mt_rand()), 0, $length) . "@" . $domain;
  }

  function curl(string $url, array $settings = []) {
    $options = [
      CURLOPT_URL            => $url,
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_POST           => true,
      CURLOPT_USERAGENT      => "Chrome/86.0.4240.110",
      CURLOPT_HTTPHEADER     => [
        "upgrade-insecure-requests: 1",
        "content-type: application/x-www-form-urlencoded",
        "cache-control: max-age=0",
        "referer: https://freewha.com/",
        "accept-language: id-ID,id;q=0.9,en-US;q=0.8,en;q=0.7",
        "accept: text/html,application/xhtml+xml,application/xml;q=0.9,image/webp,image/apng,*/*;q=0.8,application/signed-exchange;v=b3;q=0.9"
      ]
    ];

    foreach ($settings as $key => $value) {
      $options[$key] = $value;
    }

    $ch = curl_init();
    curl_setopt_array($ch, $options);
    $response = curl_exec($ch);
    curl_close($ch);

    return $response;
  }

  $climate->br()->out("
 _____     _ _ _     _____
|   __|___| | | |___|  |  |
|   __|___| | | |___|     |
|__|      |_____|   |__|__|

  -- Free Web Hosting --
 ");

  $climate->info("* Source (https://github.com/dz-id/FreeWebHosting)");
  $climate->info("* I take it from here (https://www.freewebhostingarea.com)")->br();

  $domainList = [
    "6te.net",
    "coolpage.biz",
    "eu5.org",
    "freetzi.com",
    "freeoda.com",
    "freevar.com",
    "noads.biz",
    "orgfree.com",
    "ueuo.com",
    "xp3.biz"
  ];

  $data = (function($d, $l) {
    for ($i = 1; $i < count($d) +1; $i++) :
      $l[] = [
        "no"          => $i,
        "domain name" => $d[$i-1]
      ];
    endfor;
    return $l;
  })($domainList, []);

  $climate->table($data)->br();

  $input = $climate->yellow()
    ->input("choice ?")
    ->accept(range(1, 10), true);

  $domain = ((int) $input->prompt()) -1;
  $domain = $domainList[$domain];

  $climate->br()->info("(okey) enter your subdomain name !!")->br();
  $climate->red("(information) subdomain name must be 3 characters or more.");

  while (true) {
    $input = $climate->yellow()
      ->input("subdomain name ?")
      ->accept(function($response) {
        return (strlen($response) > 2);
      });

    $thirdLevelDomain = $input->prompt();

    $response = curl("https://www.freewebhostingarea.com/cgi-bin/create_account.cgi", [
      CURLOPT_POSTFIELDS => http_build_query([
        "thirdLevelDomain" => $thirdLevelDomain,
        "domain"           => $domain,
        "action"           => "check_domain"
      ])
    ]);

    if (strpos($response, "Account already exists") !== false) {
      $climate->br()->error("(sorry) this domain '{$thirdLevelDomain}{$domain}' has been used by someone else.")->br();
      continue;
    }

    break;
  }

 $dom = new \DOMDocument();
 @$dom->loadHTML($response);

 $fields = [];

 foreach ($dom->getElementsByTagName("input") as $input) {
   if ($input->getAttribute("type") === "hidden") {
     $fields[$input->getAttribute("name")] = $input->getAttribute("value");
   }
 }

 if (count($fields) < 2) {
   return $climate->br()->error("(sorry) there is an unknown error.")->br();
 }

 $climate->br()->info("(okey) enter your password !!");

 $climate->br()->red("(information) do not use special characters or spaces.");
 $input = $climate->yellow()->input("password ?");
 $input->accept(function($response) {
   return (strlen($response) > 5 && preg_match("/^[a-zA-Z0-9]*$/", $response));
 });
 $password = $input->prompt();

 $climate->br()->red("(information) otherwise will use random email.");
 $input = $climate->yellow()->input("using your email address ?");
 $input->accept(["yes", "no"], true);

 if (strtolower($input->prompt()) === "yes") {
   $climate->br()->info("(okey) enter your email !!")->br();
   $input = $climate->yellow()->input("email address ?");
   $input->accept(function($response) {
     return (filter_var($response, FILTER_VALIDATE_EMAIL));
   });
   $email = $input->prompt();
 } else {
   $email = getRandomEmail(10);
 }

 $fields["email"]           = $email;
 $fields["password"]        = $password;
 $fields["confirmPassword"] = $password;
 $fields["agree"]           = "1";

 curl("https://newserv.freewha.com/cgi-bin/create_ini.cgi", [
   CURLOPT_POSTFIELDS => http_build_query($fields)
 ]);

 $climate->br()->info("(congratulations) your account has been successfully created. with email ({$email})");

 $climate->br()->info("cpanel login here (http://{$thirdLevelDomain}.{$domain}/cpanel)");
 $climate->out("username     : {$thirdLevelDomain}.{$domain}");
 $climate->out("password     : {$password}");

 $climate->br()->info("FTP login here (http://{$thirdLevelDomain}.{$domain}/ftp)");
 $climate->out("FTP host     : {$thirdLevelDomain}.{$domain}");
 $climate->out("FTP username : {$thirdLevelDomain}.{$domain}");
 $climate->out("FTP password : {$password}");

 $climate->br()->info("Thank for using this tools.");
})((new CLImate()));
