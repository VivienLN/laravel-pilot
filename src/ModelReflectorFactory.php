<?php
namespace VivienLN\Pilot;


class ModelReflectorFactory
{
    /** @var Config */
    protected $_config;

    /**
     * ModelReflectorFactory constructor.
     * @param array $config
     */
    public function __construct($config = null)
    {
        $this->_config = $config;
    }

    /**
     * Create a reflector from a slug
     * @param string $slug
     * @return ModelReflector|void
     * @throws \Exception
     */
    public function createFromSlug(string $slug)
    {
        $modelConfig = $this->_getModelConfig($slug);
        if(empty($modelConfig)) {
            throw new \Exception('Reflector not found for slug: '.$slug);
            return;
        }
        $reflector = new ModelReflector($modelConfig);
        return $reflector;
    }

    /**
     * Get the config array for a given model (from its slug)
     * @param string $slug
     * @return mixed|null
     */
    protected function _getModelConfig(string $slug) {
        foreach($this->_config as $c) {
            if($c['slug'] == $slug) {
                return $c;
            }
        }
        return null;
    }
}