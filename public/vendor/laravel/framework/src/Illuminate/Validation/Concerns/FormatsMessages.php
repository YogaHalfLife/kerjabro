<?php

namespace Illuminate\Validation\Concerns;

use Closure;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\File\UploadedFile;

trait FormatsMessages
{
    use ReplacesAttributes;

    /**
     * Get the validation message for an attribute and rule.
     *
     * @param  string  $attribute
     * @param  string  $rule
     * @return string
     */
    protected function getMessage($attribute, $rule)
    {
        $inlineMessage = $this->getInlineMessage($attribute, $rule);
        if (! is_null($inlineMessage)) {
            return $inlineMessage;
        }

        $lowerRule = Str::snake($rule);

        $customKey = "validation.custom.{$attribute}.{$lowerRule}";

        $customMessage = $this->getCustomMessageFromTranslator(
                in_array($rule, $this->sizeRules)
                    ? [$customKey.".{$this->getAttributeType($attribute)}", $customKey]
                    : $customKey
        );
        if ($customMessage !== $customKey) {
            return $customMessage;
        }
        elseif (in_array($rule, $this->sizeRules)) {
            return $this->getSizeMessage($attribute, $rule);
        }
        $key = "validation.{$lowerRule}";

        if ($key !== ($value = $this->translator->get($key))) {
            return $value;
        }

        return $this->getFromLocalArray(
            $attribute, $lowerRule, $this->fallbackMessages
        ) ?: $key;
    }

    /**
     * Get the proper inline error message for standard and size rules.
     *
     * @param  string  $attribute
     * @param  string  $rule
     * @return string|null
     */
    protected function getInlineMessage($attribute, $rule)
    {
        $inlineEntry = $this->getFromLocalArray($attribute, Str::snake($rule));

        return is_array($inlineEntry) && in_array($rule, $this->sizeRules)
                    ? $inlineEntry[$this->getAttributeType($attribute)]
                    : $inlineEntry;
    }

    /**
     * Get the inline message for a rule if it exists.
     *
     * @param  string  $attribute
     * @param  string  $lowerRule
     * @param  array|null  $source
     * @return string|null
     */
    protected function getFromLocalArray($attribute, $lowerRule, $source = null)
    {
        $source = $source ?: $this->customMessages;

        $keys = ["{$attribute}.{$lowerRule}", $lowerRule];
        foreach ($keys as $key) {
            foreach (array_keys($source) as $sourceKey) {
                if (str_contains($sourceKey, '*')) {
                    $pattern = str_replace('\*', '([^.]*)', preg_quote($sourceKey, '#'));

                    if (preg_match('#^'.$pattern.'\z#u', $key) === 1) {
                        return $source[$sourceKey];
                    }

                    continue;
                }

                if (Str::is($sourceKey, $key)) {
                    return $source[$sourceKey];
                }
            }
        }
    }

    /**
     * Get the custom error message from the translator.
     *
     * @param  array|string  $keys
     * @return string
     */
    protected function getCustomMessageFromTranslator($keys)
    {
        foreach (Arr::wrap($keys) as $key) {
            if (($message = $this->translator->get($key)) !== $key) {
                return $message;
            }
            $shortKey = preg_replace(
                '/^validation\.custom\./', '', $key
            );

            $message = $this->getWildcardCustomMessages(Arr::dot(
                (array) $this->translator->get('validation.custom')
            ), $shortKey, $key);

            if ($message !== $key) {
                return $message;
            }
        }

        return Arr::last(Arr::wrap($keys));
    }

    /**
     * Check the given messages for a wildcard key.
     *
     * @param  array  $messages
     * @param  string  $search
     * @param  string  $default
     * @return string
     */
    protected function getWildcardCustomMessages($messages, $search, $default)
    {
        foreach ($messages as $key => $message) {
            if ($search === $key || (Str::contains($key, ['*']) && Str::is($key, $search))) {
                return $message;
            }
        }

        return $default;
    }

    /**
     * Get the proper error message for an attribute and size rule.
     *
     * @param  string  $attribute
     * @param  string  $rule
     * @return string
     */
    protected function getSizeMessage($attribute, $rule)
    {
        $lowerRule = Str::snake($rule);
        $type = $this->getAttributeType($attribute);

        $key = "validation.{$lowerRule}.{$type}";

        return $this->translator->get($key);
    }

    /**
     * Get the data type of the given attribute.
     *
     * @param  string  $attribute
     * @return string
     */
    protected function getAttributeType($attribute)
    {
        if ($this->hasRule($attribute, $this->numericRules)) {
            return 'numeric';
        } elseif ($this->hasRule($attribute, ['Array'])) {
            return 'array';
        } elseif ($this->getValue($attribute) instanceof UploadedFile) {
            return 'file';
        }

        return 'string';
    }

    /**
     * Replace all error message place-holders with actual values.
     *
     * @param  string  $message
     * @param  string  $attribute
     * @param  string  $rule
     * @param  array  $parameters
     * @return string
     */
    public function makeReplacements($message, $attribute, $rule, $parameters)
    {
        $message = $this->replaceAttributePlaceholder(
            $message, $this->getDisplayableAttribute($attribute)
        );

        $message = $this->replaceInputPlaceholder($message, $attribute);
        $message = $this->replaceIndexPlaceholder($message, $attribute);
        $message = $this->replacePositionPlaceholder($message, $attribute);

        if (isset($this->replacers[Str::snake($rule)])) {
            return $this->callReplacer($message, $attribute, Str::snake($rule), $parameters, $this);
        } elseif (method_exists($this, $replacer = "replace{$rule}")) {
            return $this->$replacer($message, $attribute, $rule, $parameters);
        }

        return $message;
    }

    /**
     * Get the displayable name of the attribute.
     *
     * @param  string  $attribute
     * @return string
     */
    public function getDisplayableAttribute($attribute)
    {
        $primaryAttribute = $this->getPrimaryAttribute($attribute);

        $expectedAttributes = $attribute != $primaryAttribute
                    ? [$attribute, $primaryAttribute] : [$attribute];

        foreach ($expectedAttributes as $name) {
            if (isset($this->customAttributes[$name])) {
                return $this->customAttributes[$name];
            }
            if ($line = $this->getAttributeFromTranslations($name)) {
                return $line;
            }
        }
        if (isset($this->implicitAttributes[$primaryAttribute])) {
            return ($formatter = $this->implicitAttributesFormatter)
                            ? $formatter($attribute)
                            : $attribute;
        }

        return str_replace('_', ' ', Str::snake($attribute));
    }

    /**
     * Get the given attribute from the attribute translations.
     *
     * @param  string  $name
     * @return string
     */
    protected function getAttributeFromTranslations($name)
    {
        return Arr::get($this->translator->get('validation.attributes'), $name);
    }

    /**
     * Replace the :attribute placeholder in the given message.
     *
     * @param  string  $message
     * @param  string  $value
     * @return string
     */
    protected function replaceAttributePlaceholder($message, $value)
    {
        return str_replace(
            [':attribute', ':ATTRIBUTE', ':Attribute'],
            [$value, Str::upper($value), Str::ucfirst($value)],
            $message
        );
    }

    /**
     * Replace the :index placeholder in the given message.
     *
     * @param  string  $message
     * @param  string  $attribute
     * @return string
     */
    protected function replaceIndexPlaceholder($message, $attribute)
    {
        return $this->replaceIndexOrPositionPlaceholder(
            $message, $attribute, 'index'
        );
    }

    /**
     * Replace the :position placeholder in the given message.
     *
     * @param  string  $message
     * @param  string  $attribute
     * @return string
     */
    protected function replacePositionPlaceholder($message, $attribute)
    {
        return $this->replaceIndexOrPositionPlaceholder(
            $message, $attribute, 'position', fn ($segment) => $segment + 1
        );
    }

    /**
     * Replace the :index or :position placeholder in the given message.
     *
     * @param  string  $message
     * @param  string  $attribute
     * @param  string  $placeholder
     * @param  \Closure  $modifier
     * @return string
     */
    protected function replaceIndexOrPositionPlaceholder($message, $attribute, $placeholder, Closure $modifier = null)
    {
        $segments = explode('.', $attribute);

        $modifier ??= fn ($value) => $value;

        $numericIndex = 1;

        foreach ($segments as $segment) {
            if (is_numeric($segment)) {
                if ($numericIndex === 1) {
                    $message = str_ireplace(':'.$placeholder, $modifier((int) $segment), $message);
                }

                $message = str_ireplace(
                    ':'.$this->numberToIndexOrPositionWord($numericIndex).'-'.$placeholder,
                    $modifier((int) $segment),
                    $message
                );

                $numericIndex++;
            }
        }

        return $message;
    }

    /**
     * Get the word for a index or position segment.
     *
     * @param  int  $value
     * @return string
     */
    protected function numberToIndexOrPositionWord(int $value)
    {
        return [
            1 => 'first',
            2 => 'second',
            3 => 'third',
            4 => 'fourth',
            5 => 'fifth',
            6 => 'sixth',
            7 => 'seventh',
            8 => 'eighth',
            9 => 'ninth',
            10 => 'tenth',
        ][(int) $value] ?? 'other';
    }

    /**
     * Replace the :input placeholder in the given message.
     *
     * @param  string  $message
     * @param  string  $attribute
     * @return string
     */
    protected function replaceInputPlaceholder($message, $attribute)
    {
        $actualValue = $this->getValue($attribute);

        if (is_scalar($actualValue) || is_null($actualValue)) {
            $message = str_replace(':input', $this->getDisplayableValue($attribute, $actualValue), $message);
        }

        return $message;
    }

    /**
     * Get the displayable name of the value.
     *
     * @param  string  $attribute
     * @param  mixed  $value
     * @return string
     */
    public function getDisplayableValue($attribute, $value)
    {
        if (isset($this->customValues[$attribute][$value])) {
            return $this->customValues[$attribute][$value];
        }

        $key = "validation.values.{$attribute}.{$value}";

        if (($line = $this->translator->get($key)) !== $key) {
            return $line;
        }

        if (is_bool($value)) {
            return $value ? 'true' : 'false';
        }

        if (is_null($value)) {
            return 'empty';
        }

        return (string) $value;
    }

    /**
     * Transform an array of attributes to their displayable form.
     *
     * @param  array  $values
     * @return array
     */
    protected function getAttributeList(array $values)
    {
        $attributes = [];
        foreach ($values as $key => $value) {
            $attributes[$key] = $this->getDisplayableAttribute($value);
        }

        return $attributes;
    }

    /**
     * Call a custom validator message replacer.
     *
     * @param  string  $message
     * @param  string  $attribute
     * @param  string  $rule
     * @param  array  $parameters
     * @param  \Illuminate\Validation\Validator  $validator
     * @return string|null
     */
    protected function callReplacer($message, $attribute, $rule, $parameters, $validator)
    {
        $callback = $this->replacers[$rule];

        if ($callback instanceof Closure) {
            return $callback(...func_get_args());
        } elseif (is_string($callback)) {
            return $this->callClassBasedReplacer($callback, $message, $attribute, $rule, $parameters, $validator);
        }
    }

    /**
     * Call a class based validator message replacer.
     *
     * @param  string  $callback
     * @param  string  $message
     * @param  string  $attribute
     * @param  string  $rule
     * @param  array  $parameters
     * @param  \Illuminate\Validation\Validator  $validator
     * @return string
     */
    protected function callClassBasedReplacer($callback, $message, $attribute, $rule, $parameters, $validator)
    {
        [$class, $method] = Str::parseCallback($callback, 'replace');

        return $this->container->make($class)->{$method}(...array_slice(func_get_args(), 1));
    }
}
