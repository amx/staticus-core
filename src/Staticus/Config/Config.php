<?php
namespace Staticus\Config;

class Config
{
    protected $config = [];
    public function __construct($config)
    {
        $this->config = $config;
    }
    public function get($route)
    {
        $routes = explode('.', $route);
        $endPath = '';
        $config = $this->config;
        foreach ($routes as $item) {
            $endPath .= $item . '.';
            if (!isset($config[$item])) {
                throw new \RuntimeException('Unknown config route: ' . $endPath, __LINE__);
            }
            $config = $config[$item];
        }

        return $config;
    }
}