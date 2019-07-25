<?php

namespace Core;

class Html
{
    /**
     * css Load the static file for CSS Files
     *
     * @param [string] $fileName
     * @param boolean $min
     * @return void
     */
    static function css($fileName, $min = false)
    {
        if($min)
        {
            ?>
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>Content/assets/<?php echo $fileName; ?>.min.css?v=<?php echo CMS_VERSION;?>"/>
            <?php
        }
        else
        {
            ?>
            <link rel="stylesheet" href="<?php echo BASE_URL; ?>Content/assets/<?php echo $fileName; ?>.css?v=<?php echo CMS_VERSION;?>"/>
            <?php
        }
    }

    /**
     * JS load the JS Files
     *
     * @param [string] $fileName
     * @param boolean $min
     * @return void
     */
    static function js($fileName, $min = false)
    {
        if($min)
        {
            ?>
            <script src="<?php echo BASE_URL; ?>Content/assets/<?php echo $fileName; ?>.min.js?v=<?php echo CMS_VERSION;?>"></script>
            <?php
        }
        else
        {
            ?>
            <script src="<?php echo BASE_URL; ?>Content/assets/<?php echo $fileName; ?>.js?v=<?php echo CMS_VERSION;?>"></script>
            <?php
        }
    }

    /**
     * Images : load images
     *
     * @param [string] $fileName
     * @return void
     */
    static function images($fileName, $css = '', $style = '')
    {
        ?>
        <img class="<?php echo $css; ?>" src="<?php echo BASE_URL; ?>Content/assets/<?php echo $fileName; ?>" <?php echo $style; ?>/>
        <?php
    }

    /**
     * app Load the JS File for App
     *
     * @param [string] $fileName
     * @return void
     */
    static function app($fileName)
    {
        if(PRODUCTION)
        {
            ?>
            <script src="<?php echo BASE_URL; ?>Scripts/<?php echo $fileName; ?>.min.js?v=<?php echo CMS_VERSION;?>"></script>
            <?php
        }
        else
        {
            ?>
            <script src="<?php echo BASE_URL; ?>Scripts/<?php echo $fileName; ?>.js?v=<?php echo CMS_VERSION;?>"></script>
            <?php
        }
    }
}