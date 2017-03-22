<?php

namespace VivienLN\Pilot;


class Pilot
{
    private $_config;

    private $_views;
    private $_reflectorFactory;

    public $defaultIcon = 'list';

    /**
     * Pilot constructor.
     * @param array $config An array containing all of the pilot config
     */
    public function __construct(array $config) {
        $this->_config = $this->_computeConfig($config);
        $this->_reflectorFactory = new ModelReflectorFactory($this->_config['models']);
    }

    /**
     * Return the reflector factory used by pilot
     * @return ModelReflectorFactory
     */
    public function getReflectorFactory() {
        return $this->_reflectorFactory;
    }

    /**
     * Return the computed config array used by pilot, merged with default values
     * @return array
     */
    public function getConfig() {
        return $this->_config;
    }

    /**
     * Return a view name from its basename. View names can be overridden in config.
     * @param string $viewName A name known by pilot, such as: "login", "index", "table", "edit"
     * @return string The real view name. May have been overridden in config. By default, pilot::$viewName.
     * @see Pilot::getViews
     */
    public function getViewName(string $viewName) {
        $views = $this->getViews();
        return $views[$viewName] ?? null;
    }

    /**
     * Return all views used by pilot in an associative array.
     * @return array
     */
    public function getViews() {
        if(empty($this->_views)) {
            $this->_views = [
                'login' => 'pilot::login',
                'index' => 'pilot::index',
                'table' => 'pilot::table',
                'edit' => 'pilot::edit',
            ];
        }
        return $this->_views;
    }

    /**
     * Get an icon as a html string. Can be an <img> or an inline SVG.
     * @param string $icon Either an icon name known by Pilot (see readme) or a file. SVGs are returned as inline.
     * @return string HTML of the icon.
     */
    public function getIcon(string $icon) {
        // string icon => svg
        if(!str_contains($icon, '.')) {
            $icon = sprintf('%s/public/img/icons/%s.svg', __DIR__, $icon);
        }
        // get svg content or img tag
        if(preg_match('/\.svg$/i', $icon)) {
            $output = file_get_contents($icon);
        } else {
            $output = sprintf('<img src="%s" alt="" />', $icon);
        }
        return $output;
    }

    /**
     * Get models config
     * They override default routes (cf. routes.php)
     * @return Array
     */
    public function getModels() {
        return $this->_config['models'];
    }

    /**
     * Compute config to merge it with default values
     * @param $config
     * @return mixed
     */
    private function _computeConfig($config) {
        foreach($config['models'] as &$c) {
            $defaults = [
                'display' => preg_replace('/^App\\\/', '', $c['model']),
                'slug' => $this->classnameToSlug($c['model']),
                'icon' => 'list',
                'per_page' => 25,
                'with' => [],
            ];
            $c = array_merge($defaults, $c);
        }
        return $config;
    }

    /**
     * Return a default slug for the provided $classname
     * @param $classname
     * @return mixed
     */
    public function classnameToSlug($classname) {
        // App\MyNamespace\MyModel => MyNamespace\MyModel
        $output = preg_replace('/^App\\\/', '', $classname);
        // App\MyNamespace\MyModel => my_namespace-my_model
        $output = str_replace("-_", "-", snake_case(str_replace("\\", "-", $output)));
        // Return result
        return $output;
    }

}