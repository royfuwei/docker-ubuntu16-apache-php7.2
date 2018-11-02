<?php
// Application middleware

// e.g: $app->add(new \Slim\Csrf\Guard);

// use Tuupola\Middleware\HttpBasicAuthentication;
 
$container = $app->getContainer();

 
$container["jwt"] = function ($container) {
    return new StdClass;
};
// log
use Monolog\Logger;
use Monolog\Handler\RotatingFileHandler;
$logger = new Logger("slim");
$rotating = new RotatingFileHandler(__DIR__ . "/logs/slim.log", 0, Logger::DEBUG);
$logger->pushHandler($rotating);


$app->add(
    new Tuupola\Middleware\JwtAuthentication(
        [
            "attribute" => "JWT_Auth",
            "path" => ["/auth"],
            "ignore" => ["/auth/token", "/auth/not-secure"],
            "passthrough" => "/token",
            "environment" => ["HTTP_JWT_AUTH", "REDIRECT_HTTP_JWT_AUTH"],
            "header" => "Authorization",
            "regexp" => "/(.*)/",
            "secret" => "supersecretkeyyoushouldnotcommittogithub",
            "secure" => false,
            "logger" => $logger,
            "error" => function ($response, $args) use ($container) {
                $json['code'] = -1;
                $json['note'] = $args['message'];
                $json['help'] = 'http://api.geoforce.com';
            
                return $response->withStatus(401)
                                ->withHeader("Content-Type", "application/json")
                                ->write(json_encode($json, JSON_UNESCAPED_SLASHES | JSON_PRETTY_PRINT));
            },
            /* "before" => function ($request, $args) use ($container) {
                $container->log->write($args);
            } */
            /* "after" => function ($response, $args) {
                
                return $response
                    ->withRedirect("../");  
            }, */
        ]
    )
);
