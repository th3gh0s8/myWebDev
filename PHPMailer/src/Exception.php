<?php
/**
 * PHPMailer-BMH (Brazilian Portuguese Mod) - A full-featured email transport class for PHP
 *
 * @package   PHPMailer-BMH
 * @author    Marcus Bointon (Synchro/coolbru) <phpmailer@synchro.co.uk>
 * @author    Jim Jagielski (jimjag) <jimjag@gmail.com>
 * @author    Andy Prevost (codeworxtech) <codeworxtech@users.sourceforge.net>
 * @author    Brent R. Matzelle (original founder)
 * @author    Claudson Martins (claudson@php.net.br) - BMH
 * @author    Haroldo Teruya (haroldo@teruya.com.br) - BMH
 * @author    Valmir Carlos Trindade (valmir@trindade.eng.br) - BMH
 * @copyright 2001 - 2019, Brent R. Matzelle
 * @copyright 2010 - 2019, Jim Jagielski
 * @copyright 2014 - 2019, Marcus Bointon
 * @copyright 2002 - 2019, Claudson Martins, Haroldo Teruya, Valmir Carlos Trindade
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @link      https://github.com/PHPMailer/PHPMailer
 */

namespace PHPMailer\PHPMailer;

class Exception extends \Exception
{
    /**
     * Prettify error message output.
     *
     * @return string
     */
    public function errorMessage()
    {
        return '<strong>' . htmlspecialchars($this->getMessage(), ENT_COMPAT | ENT_HTML401) . "</strong><br />\n";
    }
}
