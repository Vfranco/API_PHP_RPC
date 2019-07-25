<?php

namespace Core;

use Interfaces\IForm;

class Form implements IForm
{
    /**
     * General Settings
     *
     * @var [array]
     */
    private $formSettings;

    /**
     * Context to Object
     *
     * @var [Object]
     */
    private static $_this;

    /**
     * Form Header
     *
     * @var [array]
     */
    private static $formHeader;

    /**
     * Enable Autocomplete Optional
     *
     * @var [string]
     */
    private static $autocomplete;

    /**
     * CSS Class Styles
     *
     * @var [string]
     */
    private static $css;

    /**
     * TextBox Form
     *
     * @var [array]
     */
    private static $inputs;

    /**
     * Buttons Form
     *
     * @var [array]
     */
    private static $buttons;

    /**
     * Constructor
     * 
     * return Object Context
     */
    public function __construct()
    {
        return $this;
    }

    /**
     * Main Method
     *
     * @param [array] $args
     * @return Context
     */
    static function createSimpleForm($args)
    {
        try {
            self::checkArgument($args);

            $form = new Form();
            $form->formSettings = $args;
            self::startBuild($form);

            return new static;
            
        } catch (\Exception $e) {
            print $e->getMessage();
        }
    }

    /**
     * Check the Elements of the Array
     *
     * @param [array] $args
     * @return Exception
     */
    private static function checkArgument($args)
    {
        if(empty($args) || count($args) <= 0)
            throw new \Exception('Error : Empty array or Arguments not defined');
    }

    /**
     * Start to Build the Form
     *
     * @param [object] $instance
     * @return void
     */
    private static function startBuild($instance)
    {
        self::$_this = $instance;       
    }

    /**
     * Build Head Form
     *
     * @return void
     */
    private static function buildHead()
    {
        try {
            self::checkAttributes();
        } catch (\Exception $e) {
            print $e->getMessage();
        }
    }

    /**
     * Check the Mandatory Attributes
     *
     * @return void
     */
    private static function checkAttributes()
    {
        if(!isset(self::$_this->formSettings['id']))
            throw new \Exception("Error : ID is Mandatory");

        if(!isset(self::$_this->formSettings['action']))
            throw new \Exception("Error : Action is Mandatory");

        self::$autocomplete = (isset(self::$_this->formSettings['autocomplete']) and self::$_this->formSettings['autocomplete'] == true) ? "autocomplete='on'" : "autocomplete='off'";
        self::$css = (isset(self::$_this->formSettings['css'])) ? "class='". self::$_this->formSettings['css'] ."'" : '';

        self::makeHead();
    }

    /**
     * Print form and attributes
     *
     * @return void
     */
    private static function makeHead()
    {
        print self::$formHeader = "<form id=". self::$_this->formSettings['id'] ." action=". self::$_this->formSettings['action'] ." ". self::$css ." ". self::$autocomplete .">";
    }

    /**
     * Build Form Inputs
     *
     * @param [array] $inputs
     * @return Context
     */
    public static function formInputs($inputs)
    {
        try{
            print self::buildHead();
            self::checkInputs($inputs);

            return new static;

        } catch (\Exception $e) {
            print $e->getMessage();
        }
    }

    /**
     * Check the Inputs
     *
     * @param [array] $inputs
     * @return void
     */
    private static function checkInputs($inputs)
    {
        if(empty($inputs) || count($inputs) <= 0)
            throw new \Exception("Error : Not Inputs Defined");
        else
        {
            self::$inputs = $inputs;

            try{
                self::drawInputs();
            } catch (\Exception $e){
                print $e->getMessage();
            }
        }
    }

    /**
     * Draw the Inputs on Form depend from Style Form
     *
     * @return void
     */
    private static function drawInputs()
    {
        if(empty(self::$_this->formSettings['css']))
            throw new \Exception('Error : FormStyle must be Defined');
        else
            if(self::$_this->formSettings['css'] == 'form-horizontal')
                self::formHorizontal();
            else
                self::formVertical();
    }

    /**
     * Set the Inputs Horizontal
     *
     * @return void
     */
    static function formHorizontal()
    {
        ?>
        <div class="box-body">
        <?php
            foreach(self::$inputs as $key => $value)
            {
            ?>
                <div class="form-group">
                <label class="<?php echo $value['labelCss']?>"><?php echo $key; ?></label>
                    <div class="<?php echo $value['col']?>">
                        <?php if($value['type'] == 'textarea'):?>
                            <textarea name="<?php echo $value['name']?>" class="<?php echo $value['css']?>"></textarea>
                        <?php else :?>
                            <?php if($value['type'] == 'select'):?>
                                <select name="<?php echo $value['name']?>" class="<?php echo $value['css']?>">
                                    <?php self::fillComboBox($value['data'])?>
                                </select>
                            <?php else: ?>
                                <?php if(!isset($value['readonly'])):?>
                                    <input type="<?php echo $value['type']?>" name="<?php echo $value['name']?>" class="<?php echo $value['css']?>"/>
                                <?php else :?>                            
                                    <input type="<?php echo $value['type']?>" name="<?php echo $value['name']?>" class="<?php echo $value['css']?>" <?php echo self::isReadOnly($value['readonly']) ?>/>
                                <?php endif;?>
                            <?php endif; ?>
                        <?php endif; ?>
                    </div>
                </div>                
            <?php
            }
            ?>
        </div>
        <?php
    }

    /**
     * Set the Inputs Vertical
     *
     * @return void
     */
    static function formVertical()
    {
        ?>
        <div class="box-body">
        <?php
            foreach (self::$inputs as $key => $value) 
            {
            ?>
                <div class="form-group">
                    <label class="<?php echo $value['labelCss']?>"><?php echo $key; ?></label>
                    <?php if($value['type'] == 'textarea'):?>
                        <textarea name="<?php echo $value['name']?>" class="<?php echo $value['css']?>"></textarea>
                    <?php else :?>
                        <?php if($value['type'] == 'select'):?>
                            <select name="<?php echo $value['name']?>" class="<?php echo $value['css']?>">
                                <?php self::fillComboBox($value['data'])?>
                            </select>
                        <?php else: ?>
                            <?php if(!isset($value['readonly'])):?>
                                <input type="<?php echo $value['type']?>" name="<?php echo $value['name']?>" class="<?php echo $value['css']?>"/>
                            <?php else :?>                            
                                <input type="<?php echo $value['type']?>" name="<?php echo $value['name']?>" class="<?php echo $value['css']?>" <?php echo self::isReadOnly($value['readonly']) ?>/>
                            <?php endif;?>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            <?php
            }
        ?>
        </div>
        <?php
    }

    /**
     * set Is ReadOnly
     *
     * @param [type] $status
     * @return boolean
     */
    static function isReadOnly($status)
    {
        if($status)
            return 'readonly';
    }

    /**
     * Fill ComboBox - Select
     * the data could be a resultSet
     *
     * @param [array | resultset] $data
     * @return void
     */
    static function fillComboBox($data)
    {
        try{
            self::checkDataToCombo($data);
        } catch(\Exception $e) {
            print $e->getMessage();
        }
    }

    /**
     * Check the resource or data
     *
     * @param [array | resultset] $data
     * @return void
     */
    static function checkDataToCombo($data)
    {
        if(empty($data) || count($data) <= 0)
            throw new \Exception("Error : Data Resource is mandatory");
        else
            self::fill($data);
    }

    /**
     * Put data on select
     *
     * @param [array] $data
     * @return void
     */
    static function fill($data)
    {
        print '<option value="">Seleccione una opcion ...</option>';

        foreach ($data as $key => $value)         
            if(is_numeric($key) || preg_match('/[A-Z]+\d+/', $key))
                print '<option value="'.$key.'">'.$value.'</option>';
            else
                print '<option value="'.$value.'">'.$value.'</option>';
    }

    /**
     * Set Buttons
     *
     * @param [array] $buttons
     * @return void
     */
    static function setButtons($buttons)
    {
        self::$buttons = $buttons;

        if(empty(self::$_this->formSettings['css']))
            return false;

        if(self::$_this->formSettings['css'] == 'form-horizontal')
            self::buttonsHorizontal();
        else
            self::buttonsVertical();
    }

    /**
     * Set Buttons for Horizontal Form
     *
     * @return void
     */
    static function buttonsHorizontal()
    {
        if(empty(self::$buttons) || count(self::$buttons) <= 0)
            exit('Error : Buttons are not defined');
        else
            self::makeButtonsHorizontal();
    }

    /**
     * Create the Buttons Horizontal
     *
     * @return void
     */
    static function makeButtonsHorizontal()
    {
        ?>
        <div class="box-footer">
            <?php
                foreach(self::$buttons as $key => $value)
                    if(isset($value['icon']))
                        print "<button type='". $value['type'] ."' class='". $value['css'] ."'><i class='". $value['icon'] ."'></i>  ". $key ."</button>";
                    else if(isset($value['id']))
                        print "<button id='". $value['id'] ."' type='". $value['type'] ."' class='". $value['css'] ."'>". $key ."</button>";
                    else
                        print "<button type='". $value['type'] ."' class='". $value['css'] ."'>". $key ."</button>";
            ?>
            </div>
        </form>
        <?php
    }

    /**
     * Set Buttons for Vertical Form
     *
     * @return void
     */
    static function buttonsVertical()
    {
        if(empty(self::$buttons) || count(self::$buttons) <= 0)
            exit('Error : Buttons are not defined');
        else
            self::makeButtonsVertical();
    }

    /**
     * Create the Buttons Vertical
     *
     * @return void
     */
    static function makeButtonsVertical()
    {
        ?>
        <div class="box-footer">
            <?php
                foreach(self::$buttons as $key => $value)
                    if(isset($value['icon']))
                        print "<button type='". $value['type'] ."' class='". $value['css'] ."'><i class='". $value['icon'] ."'></i>  ". $key ."</button>";
                    else if(isset($value['id']))
                        print "<button id='". $value['id'] ."' type='". $value['type'] ."' class='". $value['css'] ."'>". $key ."</button>";
                    else
                        print "<button type='". $value['type'] ."' class='". $value['css'] ."'>". $key ."</button>";
            ?>
            </div>
        </form>
        <?php
    }
}