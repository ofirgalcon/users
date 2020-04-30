<?php

use CFPropertyList\CFPropertyList;
use munkireport\processors\Processor;

class Users_processor extends Processor
{
    /**
     * Process data sent by postflight
     *
     * @param string data
     * @author tuxudo
     **/
    public function run($plist)
    {
        // Check if we have data
        if ( ! $plist){
            throw new Exception("Error Processing Request: No property list found", 1);
        }

        // Delete previous set
        Users_model::where('serial_number', $this->serial_number)->delete();

        $parser = new CFPropertyList();
        $parser->parse($plist, CFPropertyList::FORMAT_XML);

        // Get fillable items
        $fillable = array_fill_keys((new Users_model)->getFillable(), null);
        $fillable['serial_number'] = $this->serial_number;

        $save_list = [];
        foreach ($parser->toArray() as $user) {
            $save_list[] = array_replace($fillable, $user);
        }

        Users_model::insertChunked($save_list);
    }
}