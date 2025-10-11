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

class SMTP
{
    /**
     * The PHPMailer SMTP version number.
     *
     * @var string
     */
    public const VERSION = '6.6.0';

    /**
     * SMTP line break constant.
     *
     * @var string
     */
    public const LE = "\r\n";

    /**
     * The SMTP port to use if one is not specified.
     *
     * @var int
     */
    public $Port = 25;

    /**
     * The connection timeout, in seconds.
     *
     * @var int
     */
    public $Timeout = 300;

    /**
     * The timeout for waiting for reply from server.
     *
     * @var int
     */
    public $Timelimit = 300;

    /**
     * The PHPMailer SMTP debug output level.
     *
     * @var int
     */
    public $do_debug = 0;

    /**
     * How to handle debug output.
     *
     * @var string
     */
    public $Debugoutput = 'echo';

    /**
     * Whether to use VERP.
     *
     * @var bool
     */
    public $do_verp = false;

    /**
     * The socket for the server connection.
     *
     * @var resource
     */
    protected $smtp_conn;

    /**
     * The most recent reply from the server.
     *
     * @var string
     */
    protected $last_reply = '';

    /**
     * The most recent error message.
     *
     * @var array
     */
    protected $error = [];

    /**
     * The reply codes that affected the last error.
     *
     * @var array
     */
    protected $error_codes = [];

    /**
     * The list of EHLO keywords.
     *
     * @var array
     */
    protected $ehlo_keywords = [];

    /**
     * The connection options for stream_socket_client.
     *
     * @var array
     */
    protected $options = [];

    /**
     * The connection context for stream_socket_client.
     *
     * @var array
     */
    protected $stream_context;
}
