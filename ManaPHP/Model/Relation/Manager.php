<?php
namespace ManaPHP\Model\Relation;

use ManaPHP\Component;
use ManaPHP\Model\Relation;

class Manager extends Component
{
    /**
     * @var array[]
     */
    protected $_relations;

    /**
     * @param \ManaPHP\Model $model
     * @param string         $name
     *
     * @return bool
     */
    public function has($model, $name)
    {
        return $this->get($model, $name) !== false;
    }

    /**
     * @param string $str
     *
     * @return string|false
     */
    protected function _pluralToSingular($str)
    {
        if ($str[strlen($str) - 1] !== 's') {
            return false;
        }

        //https://github.com/UlvHare/PHPixie-demo/blob/d000d8f11e6ab7c522feeb4457da5a802ca3e0bc/vendor/phpixie/orm/src/PHPixie/ORM/Configs/Inflector.php
        if (preg_match('#^(.*?us)$|(.*?[sxz])es$|(.*?[^aeioudgkprt]h)es$#', $str, $match)) {
            return $match[1];
        } elseif (preg_match('#^(.*?[^aeiou])ies$#', $str, $match)) {
            return $match[1] . 'y';
        } else {
            return substr($str, 0, -1);
        }
    }

    /**
     * @param \ManaPHP\Model $model
     * @param string         $plainName
     *
     * @return string|false
     */
    protected function _inferClassName($model, $plainName)
    {
        $modelName = get_class($model);

        if (($pos = strrpos($modelName, '\\')) !== false) {
            $className = substr($modelName, 0, $pos + 1) . ucfirst($plainName);
        } else {
            $className = ucfirst($plainName);
        }

        return class_exists($className) ? $className : false;
    }

    /**
     * @param \ManaPHP\Model $model
     * @param string         $name
     *
     * @return  array|false
     */
    protected function _inferRelation($model, $name)
    {
        if (preg_match('#^(.+[a-z\d])Of([A-Z].*)$#', $name, $match)) {
            if (!$singular = $this->_pluralToSingular($match[1])) {
                return false;
            }

            if (!$className = $this->_inferClassName($model, $singular)) {
                return false;
            }

            $valueField = lcfirst($match[2]) . '_id';
            if (in_array($valueField, $model->getForeignKeys(), true)) {
                /**
                 * @var \ManaPHP\Model $reference
                 */
                $reference = new $className;
                return [$className, Relation::TYPE_HAS_MANY_TO_MANY, $reference->getPrimaryKey(), $valueField];
            } else {
                return false;
            }
        }

        if ($singular = $this->_pluralToSingular($name)) {
            if (!$className = $this->_inferClassName($model, $singular)) {
                return false;
            }

            /**
             * @var \ManaPHP\Model $reference
             */
            $reference = new $className;

            $keys = $model->getForeignKeys();
            if (count($keys) === 2) {
                $foreignKey = $singular . '_id';
                if (in_array($foreignKey, $keys, true)) {
                    $keys = array_flip($keys);
                    unset($keys[$foreignKey]);
                    return [$className, Relation::TYPE_HAS_MANY_TO_MANY, $reference->getPrimaryKey(), key($keys)];
                }
            }
            return [$className, Relation::TYPE_HAS_MANY];
        } else {
            if (in_array($name . '_id', $model->getFields(), true)) {
                $className = $this->_inferClassName($model, $name);
                return $className ? [$className, Relation::TYPE_HAS_ONE] : false;
            }
        }

        return false;
    }

    /**
     * @param string $str
     *
     * @return bool
     */
    protected function _isPlural($str)
    {
        if ($str[strlen($str) - 1] !== 's') {
            return false;
        }

        //https://github.com/UlvHare/PHPixie-demo/blob/d000d8f11e6ab7c522feeb4457da5a802ca3e0bc/vendor/phpixie/orm/src/PHPixie/ORM/Configs/Inflector.php
        return preg_match('#us|[sxz]es|[^aeioudgkprt]hes|[^aeiou]ies$#', $str) === 1;
    }

    /**
     * @param \ManaPHP\Model $model
     * @param string         $name
     *
     * @return \ManaPHP\Model\Relation|false
     */
    public function get($model, $name)
    {
        $modelName = get_class($model);

        if (!isset($this->_relations[$modelName])) {
            $this->_relations[$modelName] = $model->relations();
            foreach ($this->_relations[$modelName] as $k => $v) {
                if (is_int($k)) {
                    $this->_relations[$modelName][$v] = [];
                    unset($this->_relations[$modelName][$k]);
                }
            }
        }

        if (!isset($this->_relations[$modelName][$name]) || !$this->_relations[$modelName][$name]) {
            if ($relation = $this->_inferRelation($model, $name)) {
                $this->_relations[$modelName][$name] = $relation;
            }
        }

        if (isset($this->_relations[$modelName][$name])) {
            $relation = $this->_relations[$modelName][$name];
            if ($relation instanceof Relation) {
                return $relation;
            } else {
                if (is_string($relation)) {
                    $relation = [$relation];
                }

                if (!isset($relation[1])) {
                    $relation[1] = $this->_isPlural($name) ? Relation::TYPE_HAS_MANY : Relation::TYPE_HAS_ONE;
                }
                return $this->_relations[$modelName][$name] = new Relation($model, $relation);
            }
        }

        return false;
    }
}