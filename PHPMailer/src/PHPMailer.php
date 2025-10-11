<?php
/**
 * PHPMailer - A full-featured email transport class for PHP
 * 
 * @package   PHPMailer
 * @author    Marcus Bointon (Synchro/coolbru) <phpmailer@synchro.co.uk>
 * @author    Jim Jagielski (jimjag) <jimjag@gmail.com>
 * @author    Andy Prevost (codeworxtech) <codeworxtech@users.sourceforge.net>
 * @author    Brent R. Matzelle (original founder)
 * @copyright 2001 - 2022, Brent R. Matzelle
 * @copyright 2010 - 2022, Jim Jagielski
 * @copyright 2014 - 2022, Marcus Bointon
 * @license   http://www.gnu.org/copyleft/lesser.html GNU Lesser General Public License
 * @link      https://github.com/PHPMailer/PHPMailer
 */

namespace PHPMailer\PHPMailer;

/**
 * PHPMailer - A full-featured email transport class for PHP.
 */
class PHPMailer
{
    // Email priority
    public const PRIORITY_HIGH = 1;
    public const PRIORITY_NORMAL = 3;
    public const PRIORITY_LOW = 5;

    // Character sets
    public const CHARSET_ASCII = 'us-ascii';
    public const CHARSET_ISO88591 = 'iso-8859-1';
    public const CHARSET_UTF8 = 'utf-8';

    // Content-type
    public const CONTENT_TYPE_PLAINTEXT = 'text/plain';
    public const CONTENT_TYPE_TEXT_CALENDAR = 'text/calendar';
    public const CONTENT_TYPE_TEXT_HTML = 'text/html';
    public const CONTENT_TYPE_MULTIPART_ALTERNATIVE = 'multipart/alternative';
    public const CONTENT_TYPE_MULTIPART_MIXED = 'multipart/mixed';
    public const CONTENT_TYPE_MULTIPART_RELATED = 'multipart/related';

    // Encryption
    public const ENCRYPTION_STARTTLS = 'tls';
    public const ENCRYPTION_SMTPS = 'ssl';

    // Encoding
    public const ENCODING_7BIT = '7bit';
    public const ENCODING_8BIT = '8bit';
    public const ENCODING_BASE64 = 'base64';
    public const ENCODING_BINARY = 'binary';
    public const ENCODING_QUOTED_PRINTABLE = 'quoted-printable';

    /**
     * The PHPMailer Version number.
     *
     * @var string
     */
    public const VERSION = '6.6.0';

    /**
     * The SMTP class instance.
     *
     * @var SMTP
     */
    protected $smtp;
}
