<?php

namespace VivienLN\Pilot;

use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Relation;

class ModelReflector implements \ArrayAccess
{
    /** @var array */
    private $_config;
    /** @var Pilot */
    protected $_pilot;
    /** @var Model */
    protected $_modelBuilder;

    /**
     * ModelReflector constructor.
     * @param array $config An array of Pilot config
     */
    public function __construct(array $config)
    {
        $pilot = app(Pilot::class);
        $this->_pilot = $pilot;
        $this->_config = $config;
        $modelBuilder = $this->getModelBuilder();

        // if no 'columns' in model config, take them from DB
        $configColumns = $this->getConfig('columns');
        if(empty($configColumns)) {
            $configColumns = resolve('db')->getSchemaBuilder()->getColumnListing((new $modelBuilder)->getTable());
        }

        // transform $configColumns to allow
        // ['foo' => [...], 'bar'] and transform it into
        // ['foo' => [...], 'bar' => []]
        $tempColumns = [];
        foreach($configColumns as $k => $configCol) {
            if(is_int($k)) {
                $tempColumns[$configCol] = [];
            } else {
                $tempColumns[$k] = $configCol;
            }
        }
        $configColumns = $tempColumns;

        // setup defaults for columns
        array_walk($configColumns, function(&$col, $colName) {
            $col = array_merge([
                'display' => $colName,
                'filters' => null,
                'show_in_table' => true,
                'form' => 'text',
                'editable' => true,
                'related_attribute' => null,
            ], $col);
        });

        $this->_config['columns'] = $configColumns;
    }

    /**
     * Filter an item property value for display
     * @param Model $item The model
     * @param string $prop The property name on the model
     * @return string Html string for this property value
     */
    public function filter(Model $item, string $prop)
    {
        // if it is a related model
        // $value becomes the related model attribute corresponding to "related_attribute" in config
        $relatedAttribute = $this->getColumnConfig($prop, 'related_attribute');
        if($relatedAttribute) {
            if(is_a($item->$prop, Model::class)) {
                // get wanted attribute of related model
                $value = $item->$prop->$relatedAttribute;
            } elseif(is_a($item->$prop, Collection::class)) {
                // get wanted attribute of ALL related models
                $values = [];
                foreach($item->$prop as $related) {
                    $values[] = $related->$relatedAttribute;
                }
                $value = implode(', ', $values);
            }
        }
        // if not related model, get related value
        $value = $value ?? strip_tags($item->$prop);
        $value = $this->_applyFilterHook('before', $item, $prop, $value);
        $value = $this->_applyFilter($item, $prop, $value, 'filters');
        $value = $this->_applyFilterHook('after', $item, $prop, $value);
        return (string)$value;
    }

    /**
     * Filter the $config['columns'] array to only keep the one with "show_in_table" set to true.
     * Used in "table" view
     * @return array
     */
    public function getTableColumns()
    {
        return array_filter($this->_config['columns'], function($col) {
            return $col['show_in_table'];
        });
    }

    /**
     * Apply a hook for the filter() method. Apply a custom callback to $value.
     * @param string $hookName The name of the hook (before/after)
     * @param Model $item The current model
     * @param string $prop The property name
     * @param mixed $value The property value
     * @return mixed The altered value
     */
    private function _applyFilterHook(string $hookName, Model $item, string $prop, $value)
    {
        $hook = $this->getColumnConfig($prop, sprintf('filters_%s', $hookName));
        if(is_callable($hook)) {
            return $hook($item, $value, $this);
        }
        return $value;
    }

    /**
     * @param Model $item The current model
     * @param string $prop The property name
     * @param mixed $value The property value
     * @param null Array $default Default filters, used if none is provided in the config (can be null)
     * @return string
     */
    private function _applyFilter(Model $item, string $prop, $value, $default = null)
    {
        // Empty value
        if(empty($value)) {
            return '';
        }
        // Empty filter
        $filters = $this->getColumnConfig($prop, 'filters');
        if(empty($filters)) {
            if(!empty($default)) {
                $filters = $default;
            } else {
                return $value;
            }
        }
        // Filter can be array or string (if only one)
        if(!is_array($filters)) {
            $filters = [$filters];
        }
        // apply filters
        foreach($filters as $filter) {
            // get filter name and args
            $explode = explode(':', $filter);
            $filterName = $explode[0];
            $param = empty($explode[1]) ? null : $explode[1];
            $method = '_filter'.ucfirst($filterName);
            // TODO: use magic methods: http://php.net/manual/fr/language.oop5.overloading.php#object.call
            if(method_exists($this, $method)) {
                $value = $this->$method($item, $value, $param);
            }
        }
        // return
        return $value;
    }

    /**
     * Get the form input view for a prop
     * @param Model $item
     * @param string $prop
     * @param bool $canSave
     * @return View
     */
    public function getFormView(Model $item, string $prop, bool $canSave = false)
    {
        // get col config
        $col = $this->getColumnConfig($prop);
        $viewData = [
            'prop' => $prop,
            'readonly' => !$col['editable'] || !$canSave,
            'display' => $col['display'],
        ];
        // get form from config
        // view name and form params are colon-separated
        // NB: $viewName may be overridden if relation
        $parts = explode(':', $col['form']);
        $viewName = $parts[0];
        if(count($parts) > 1) {
            $formParams = explode(',', $parts[1]);
        }
        // is it a relation?
        // In this case form and values are fetched from it
        $relation = $this->getRelation($item, $prop);
        if($relation) {
            $related = $relation->getRelated();
            if(is_a($relation, BelongsTo::class)) {
                $viewName = 'related';
                $value = $item->$prop ? $item->$prop->getKey() : null;
            } else {
                $viewName = 'related_multiple';
                $value = $item->$prop ? $item->$prop->pluck($related->getKeyName())->toArray() : [];
            }
            // Then get all possible values as params
            $formParams = $related::all()->pluck($col['related_attribute'], $related->getKeyName())->toArray();
        } else {
            // Not a relation, get simple value
            $value = $item->$prop;
        }
        // set value and params
        $viewData['value'] = old($prop) ?? $value ?? null;
        $viewData['params'] = $formParams ?? null;
        // return view  
        $view = sprintf('pilot::partials.edit.%s', $viewName);
        return view($view, $viewData);
    }

    /**
     * @param $item
     * @param $prop
     * @return Relation|null
     */
    public function getRelation($item, $prop)
    {
        if(method_exists($item, $prop) && is_a($item->$prop(), Relation::class)) {
            return $item->$prop();
        }
        return null;
    }

    /**
     * Get the validation rules from config
     * @return array
     */
    public function getValidationRules()
    {
        $output = [];
        foreach($this['columns'] as $colName => $col) {
            if(empty($col['rules'])) {
                continue;
            }
            $output[$colName] = $col['rules'];
        }
        return $output;
    }

    /**
     * @return array
     */
    public function getValidationMessages()
    {
        /*
            Turn this: "title" => ["required" => "...", "min" => "..."]
            Into this: "title.required" => "...", "title.min" => "..."
        */
        $output = [];
        foreach($this['columns'] as $colName => $col) {
            if(empty($col['messages'])) {
                continue;
            }
            foreach($col['messages'] as $messageKey => $messages) {
                $finalKey = $colName.'.'.$messageKey;
                $output[$finalKey] = $messages;
            }
        }
        return $output;
    }

    /**
     * Filter: apply a limit. Truncate $value and add "..." at the end.
     * @param $item
     * @param $value
     * @param null $param
     * @return string
     */
    private function _filterLimit($item, $value, $param = null)
    {
        return str_limit($value, $param, '...');
    }

    /**
     * Filter: return the HTML string for an asset (img tag)
     * @param $item
     * @param $value
     * @param null $param
     * @return string
     */
    private function _filterAsset($item, $value, $param = null)
    {
        return sprintf('<img src="%s" %s/>', asset($value), ($param ? ('width="'.$param.'"') : ''));
    }

    /**
     * Return $value as a link to the $item edit (or view) form
     * @param $item
     * @param $value
     * @param null $param
     * @return mixed
     */
    private function _filterLink($item, $value, $param = null)
    {
        $href = url(sprintf('%s/%s/edit/%s', config('pilot.prefix'), $this->getConfig('slug'), $item->getKey()));
        return sprintf('<a href="%s">%s</a>', $href, $value);
    }

    /**
     * List all available reflectors with slugs as keys
     * @return Array ModelReflector objects
     */
    public static function createArray($config)
    {
        $reflectors = [];
        foreach($config as $c) {
            // TODO: throw error if $c['model'] is empty!
            $reflector = new ModelReflector($c);
            $reflectors[$reflector->getConfig('slug')] = $reflector;
        }

        return $reflectors;
    }

    /**
     * Return the query builder related to this reflector
     * @return Model
     */
    public function getModelBuilder()
    {
        if(empty($this->_modelBuilder)) {
            $builder = resolve($this->getConfig('model'));
            $this->_modelBuilder = is_a($builder, Model::class) ? $builder : null;
        }
        return $this->_modelBuilder;
    }


    /**
     * Find a model with its primary key
     * @param $id
     * @return Model|null
     */
    public function findModel($id)
    {
        $builder = $this->getModelBuilder();
        return $builder ? $builder->find($id) : null;
    }

    /**
     * eager load related data for $items, according to config file
     * @param Collection $items
     * @param array $relationships
     * @return Collection
     */
    public function eagerLoad(Collection $items)
    {
        $relationships = $this->getConfig('with');
        return $items->load($relationships);
    }

    /**
     * Get a config value from its key
     * @param null $key
     * @return array|mixed|null
     */
    public function getConfig($key = null)
    {
        if(!empty($key)) {
            if(array_key_exists($key, $this->_config)) {
                return $this->_config[$key];
            } else {
                return null;
            }
        }
        return $this->_config;
    }

    /**
     * Get config array for a given column ($prop) or get value if $configKey is provided
     * @param $prop
     * @param $configKey
     * @return the config value(s)
     */
    public function getColumnConfig($prop, $configKey = null)
    {
        $columns = $this->getConfig('columns');
        if(is_array($columns) && array_key_exists($prop, $columns)) {
            if(empty($configKey)) {
                return $columns[$prop];
            } elseif(array_key_exists($configKey, $columns[$prop])) {
                return $columns[$prop][$configKey];
            }
        }
        return null;
    }

    /**
     * ArrayAccess implementation
     * @param mixed $offset
     * @return mixed
     */
    public function offsetExists($offset)
    {
        return array_key_exists($offset, $this->getConfig());
    }

    /**
     * ArrayAccess implementation
     * @param mixed $offset
     * @return mixed
     */
    public function offsetGet($offset)
    {
        return $this->getConfig($offset);
    }

    /**
     * ArrayAccess implementation
     * @param mixed $offset
     * @param mixed $value
     * @return mixed
     */
    public function offsetSet($offset, $value)
    {
        return $this->_config[$offset] = $value;
    }

    /**
     * ArrayAccess implementation
     * @param mixed $offset
     * @return mixed
     */
    public function offsetUnset($offset)
    {
        unset($this->_config[$offset]);
    }

}