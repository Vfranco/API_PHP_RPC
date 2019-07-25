<?php

namespace AppLib\Http;

class Redirect
{
    static function RedirectToHome()
    {
        ?>
        <script type="text/javascript">location.href = '<?php echo BASE_URL; ?>'</script>
        <?php
    }

    static function RedirectTo($arg)
    {
        header("Location:" . BASE_URL . $arg);
    }
}