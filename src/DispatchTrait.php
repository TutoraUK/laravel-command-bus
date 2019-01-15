<?php

namespace TutoraUK\CommandBus;

trait DispatchTrait
{
    /**
     * @param  string  $command
     * @param  \ArrayAccess  $source
     * @param  \ReflectionParameter  $parameter
     * @param  array  $extras
     * @return mixed
     */
    protected function getParameterValueForCommand($command, \ArrayAccess $source, \ReflectionParameter $parameter, array $extras = [])
    {
        if (array_key_exists($parameter->name, $extras)) {
            return $extras[$parameter->name];
        }
        if (isset($source[$parameter->name])) {
            return $source[$parameter->name];
        }
        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }
        throw new \RuntimeException("Missing parameter $parameter in " . get_class($command));
    }

    /**
     * Marshal a command and dispatch it to its appropriate handler.
     *
     * @deprecated Provides backward compatibility for old commands.
     * @param  mixed $command
     * @param  array $array
     * @return mixed
     * @throws \ReflectionException
     */
    public function dispatchFromArray($command, array $array)
    {
        return $this->dispatchCommand($this->marshalFromArray($command, $array));
    }

    /**
     * Marshal a command and dispatch it to its appropriate handler.
     *
     * @deprecated Provides backward compatibility for old commands.
     * @param  mixed $command
     * @param  \ArrayAccess $source
     * @param  array $extras
     * @return mixed
     * @throws \ReflectionException
     */
    public function dispatchFrom($command, \ArrayAccess $source, array $extras = [])
    {
        return $this->dispatchCommand($this->marshal($command, $source, $extras));
    }

    /**
     * Marshal a command from the given array.
     *
     * @param  string $command
     * @param  array $array
     * @return Command
     * @throws \ReflectionException
     */
    protected function marshalFromArray($command, array $array)
    {
        return $this->marshal($command, new \Collection, $array);
    }

    /**
     * Marshal a command from the given array accessible object.
     *
     * @param  string $command
     * @param  \ArrayAccess $source
     * @param  array $extras
     * @return Command
     * @throws \ReflectionException
     */
    protected function marshal($command, \ArrayAccess $source, array $extras = [])
    {
        $injected = [];
        $reflection = new \ReflectionClass($command);
        if ($constructor = $reflection->getConstructor()) {
            $injected = array_map(function ($parameter) use ($command, $source, $extras) {
                return $this->getParameterValueForCommand($command, $source, $parameter, $extras);
            }, $constructor->getParameters());
        }
        return $reflection->newInstanceArgs($injected);
    }

    /**
     * Dispatches a command to the command bus
     *
     * @deprecated Provides backward compatibility for old commands.
     * @param Command $command
     * @return mixed
     */
    public function dispatchCommand(Command $command) {
        /* @var $dispatcher CommandBus */
        $dispatcher = resolve(CommandBus::class);

        return $dispatcher->execute($command);
    }
}