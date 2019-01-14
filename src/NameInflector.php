<?php

namespace TutoraUK\CommandBus;

class NameInflector implements Inflector
{
    /**
     * Find a Handler for a Command
     *
     * @param Command $command
     * @return string
     */
    public function inflect(Command $command)
    {
        return str_replace('\Commands', '\Handlers\Commands', get_class($command)) . 'Handler';
    }
}
