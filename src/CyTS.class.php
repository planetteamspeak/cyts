<?php

/**
 * CyTS.class.php
 * -----------------------------------------------------------------------
 * begin         : Saturday, Oct 16, 2004
 * copyright     : (c) 2004-2009 Steven Barth
 * email         : cyrus@planetteamspeak.com
 * version       : 2.5.2
 * last modified : Friday, September 11, 2009
 * 
 * CyTS - A PHP library for querying TeamSpeak 2 servers
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

/* version information */
define("CYTS_VERSION", "2.5.2");
define("CYTS_BUILD", "EPSILON");

/* error messages */
define("CYTS_INVALID_ID", "ERROR, invalid id");
define("CYTS_INVALID_PID", "ERROR, Playerid does not exist");
define("CYTS_INVALID_CID", "ERROR, Channelid does not exist");
define("CYTS_INVALID_AUTH", "ERROR, not logged in");
define("CYTS_INVALID_DATA", "ERROR, no data available");
define("CYTS_INVALID_PORT", "ERROR, port already in use");
define("CYTS_INVALID_MOVE", "ERROR, Allready member of channel");
define("CYTS_INVALID_INIT", "ERROR, unable to initialize server");
define("CYTS_INVALID_ERROR", "ERROR, error");
define("CYTS_INVALID_PARAM", "ERROR, invalid paramcount");
define("CYTS_INVALID_PERMS", "ERROR, invalid permissions");
define("CYTS_INVALID_LOGIN", "ERROR, invalid login");
define("CYTS_INVALID_START", "ERROR, server is running");
define("CYTS_INVALID_SERVER", "ERROR, no server selected");
define("CYTS_INVALID_NUMBER", "ERROR, invalid number format");
define("CYTS_INVALID_PASSWD", "ERROR, passwort dont match");
define("CYTS_INVALID_ACCESS", "Your password failed 3 consecutive times, please wait 10 minutes before trying again!");
define("CYTS_INVALID_BANNED", "You are still banned or a failed password try caused a 30 second delay!");
define("CYTS_ERROR", "error");
define("CYTS_SYN", "[TS]");
define("CYTS_OK", "OK");

/* debug levels */
define("CYTS_DEBUG_ERROR", 0x01);
define("CYTS_DEBUG_WARN", 0x02);
define("CYTS_DEBUG_INFO", 0x03);

/* command parameters */
define("CYTS_MOVE", 0x01);
define("CYTS_KICK", 0x02);
define("CYTS_REMOVE", 0x03);

define("CYTS_FLAG_CPRIV", 0x01);
define("CYTS_FLAG_PPRIV", 0x02);
define("CYTS_FLAG_PFLAG", 0x03);
define("CYTS_FLAG_CFLAG", 0x04);

/* target parameters */
define("CYTS_UNREGUSER", 0x01);
define("CYTS_REGUSER", 0x02);
define("CYTS_UNREGCHANNEL", 0x04);
define("CYTS_REGCHANNEL", 0x08);

/**
 * @package   CyTS
 * @version   2.5.2
 * @author    Steven Barth <cyrus@planetteamspeak.com>
 * @author    Sven Paulsen <scp@planetteamspeak.com>
 * @copyright Copyright (c) 2004-2009 Steven Barth
 */
class CyTS
{
  /**
   * The stream resource for the TCP Query connection.
   *
   * @var resource
   */
  var $sCon = null;

  /**
   * The IPv4 address or FQDN of the TeamSpeak server.
   *
   * @var string
   */
  var $server = null;

  /**
   * The TCP Query port of the TeamSpeak server.
   *
   * @var integer
   */
  var $tcp = null;

  /**
   * The UDP port of the virtual TeamSpeak server.
   *
   * @var integer
   */
  var $udp = null;

  /**
   * The username used to authenticate with the server.
   *
   * @var string
   */
  var $user = null;

  /**
   * The password used to authenticate with the server.
   *
   * @var string
   */
  var $pass = null;
  
  /**
   * The ID of the selected virtual server.
   *
   * @var integer
   */
  var $sid = null;
  
  /**
   * The serverinfo data of the selected virtual server.
   *
   * @var integer
   */
  var $sInfo = null;

  /**
   * Indicates wether we're authenticated as SA or not.
   *
   * @var boolean
   */
  var $isAdmin = FALSE;

  /**
   * Indicates wether we're authenticated as SSA or not.
   *
   * @var boolean
   */
  var $isSAdmin = FALSE;

  /**
   * The last playerlist received from the server.
   *
   * @var array
   */
  var $uList = null;

  /**
   * The last channellist received from the server.
   *
   * @var array
   */
  var $cList = null;

  /**
   * The last serverlist received from the server.
   *
   * @var array
   */
  var $sList = null;

  /**
   * The last dbuserlist received from the server.
   *
   * @var array
   */
  var $dbuList = null;

  /**
   * The last dbsuserlist received from the server.
   *
   * @var array
   */
  var $dbsuList = null;

  /**
   * The last dbserverlist received from the server.
   *
   * @var array
   */
  var $dbsList = null;
  
  /**
   * The last server description received from the server.
   *
   * @var array
   */
  var $sDescription = null;

  /**
   * The servers version information.
   *
   * @var array
   */
  var $sVersion = null;

  /**
   * The HTTP port of the TeamSpeak servers web admin interface.
   *
   * @var integer
   */
  var $wiPort = null;

  /**
   * The HTTP Cookie for the TeamSpeak servers web admin interface.
   *
   * @var string
   */
  var $wiCookie = null;

  /**
   * Various debugging information.
   *
   * @var array
   */
  var $debug = array();

  /**
   * Compatibility constructor function used by PHP < 5.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @return  CyTS
   */
  function CyTS()
  {
    $this->__construct();
  }

  /**
   * Constructor function.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @return  cyts
   */
  function __construct()
  {
    if(!function_exists("fsockopen")) {
      trigger_error("Network functions are not available", E_USER_ERROR);
    }
  }

  /**
   * Destructor function.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @return  void
   */
  function __destruct()
  {
    $this->disconnect();
  }

  /**
   * Connects to a specified TeamSpeak server via TCP Query.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   string $sIP
   * @param   integer $sTCP
   * @param   integer $sUDP
   * @param   integer $sTimeout
   * @return  boolean
   */
  function connect($sIP, $sTCP, $sUDP = null, $sTimeout = 3)
  {
    $this->sCon = @fsockopen($sIP, $sTCP, $errNo, $errStr, $sTimeout);

    if(!$this->sCon) {
      $this->_debug(null, "TCP Query connection error " . $errNo . " (" . $errStr . ")", CYTS_DEBUG_ERROR);
      return FALSE;
    }

    $this->server = $sIP;
    $this->tcp = $sTCP;

    if($this->_readline() != CYTS_SYN) {
      $this->_debug(null, "Host is not a TeamSpeak server", CYTS_DEBUG_ERROR);
      $this->disconnect();
      return FALSE;
    }

    $sVer = $this->info_serverVersion();

    if($sVer["release"] < 23 && $sVer["build"] < 14) {
      $this->_debug(null, "Incompatible server version (" . $sVer["total"] . ")", CYTS_DEBUG_ERROR);
      $this->disconnect();
      return FALSE;
    }
    
    if($sUDP && !$this->select($sUDP)) {
      $this->disconnect();
      return FALSE;
    }

    return TRUE;
  }

  /**
   * Disconnects from a TeamSpeak servers TCP Query interface.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @return  void
   */
  function disconnect()
  {
    if(!$this->_connected()) {
      return;
    }

    $this->_writecall("quit");

    unset($this->sCon);
  }

  /**
   * Authenticates with the server as SA.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   string  $sUser
   * @param   string  $sPass
   * @return  boolean
   */
  function login($sUser, $sPass)
  {
    if($this->_extendcall("login " . $sUser . " " . $sPass) != CYTS_OK) {
      $this->_debug(null, "Invalid username or password", CYTS_DEBUG_ERROR);
      return FALSE;
    }

    $this->user = $sUser;
    $this->pass = $sPass;

    $this->isAdmin = TRUE;
    $this->isSAdmin = FALSE;
    
    $this->_debug(null, "Permission level changed to SA", CYTS_DEBUG_WARN);

    return TRUE;
  }

  /**
   * Authenticates with the server as SSA.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   string  $sUser
   * @param   string  $sPass
   * @return  boolean
   */
  function slogin($sUser, $sPass)
  {
    if($this->_extendcall("slogin " . $sUser . " " . $sPass) != CYTS_OK) {
      $this->_debug(null, "Invalid username or password", CYTS_DEBUG_ERROR);
      return FALSE;
    }

    $this->user = $sUser;
    $this->pass = $sPass;

    $this->isAdmin = TRUE;
    $this->isSAdmin = TRUE;
    
    $this->_debug(null, "Permission level changed to SA", CYTS_DEBUG_WARN);

    return TRUE;
  }

  /**
   * Selects a virtual TeamSpeak server.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $sUDP
   * @return  boolean
   */
  function select($sUDP)
  {
    if($this->_extendcall("sel " . $sUDP) != CYTS_OK) {
      $this->_debug(null, "Invalid virtual server port (" .  $sUDP . ")", CYTS_DEBUG_ERROR);
      return FALSE;
    }

    $sInfo = $this->info_serverInfo();
    
    $this->udp = $sUDP;
    $this->sid = $sInfo["server_id"];
    
    $this->_debug(null, "Virtual server port " . $this->udp . " (ID " . $this->sid . ") selected", CYTS_DEBUG_WARN);
    
    return TRUE;
  }

  /**
   * Returns a list of clients connected to the virtual TeamSpeak server.
   * 
   * array:
   * [0], [unparsed]   => Unparsed string
   * [1], [p_id]       => Player ID
   * [2], [c_id]       => Channel ID
   * [3], [ps]         => Packets sent by server
   * [4], [bs]         => Bytes sent by server
   * [5], [pr]         => Packets received by server
   * [6], [br]         => Bytes received by server
   * [7], [pl]         => Packet loss
   * [8], [ping]       => Ping
   * [9], [logintime]  => Seconds since Login
   * [10], [idletime]  => Idletime in seconds
   * [11], [cprivs]    => Channel privs
   * [12], [pprivs]    => Global privs
   * [13], [pflags]    => Player flags
   * [14], [ip]        => IP address
   * [15], [nick]      => Nickname
   * [16], [loginname] => Loginname
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   boolean $force
   * @return  array
   */
  function info_playerList($force = FALSE)
  {
    if($force === FALSE && is_array($this->uList)) {
      return $this->uList;
    }

    $this->uList = $this->_fetchtable($this->_fastcall("pl"));
        
    if(is_array($this->uList)) {
      usort($this->uList, array(__CLASS__, "_sortplayers"));
    }

    return $this->uList;
  }

  /**
   * Returns the names of all players connected to the virtual server.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   boolean $force
   * @return  array
   */
  function info_playerNameList($force = FALSE)
  {
    if(!$this->info_playerList($force)) {
      return array();
    }

    $nList = array();

    foreach($this->uList as $player) {
      $nList[$player[1]] = $player[15];
    }

    return $nList;
  }

  /**
   * Returns the ID of the player using the specified nickname.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   string  $pName
   * @param   boolean $force
   * @return  integer
   */
  function info_getPlayerByName($pName, $force = FALSE)
  {
    if(!$this->info_playerList($force)) {
      return -1;
    }

    foreach($this->uList as $player) {
      if($player[15] == $pName) return $player[1];
    }

    return -1;
  }

  /**
   * Returns playerlist information about the specified player.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $pID
   * @param   boolean $force
   * @return  array
   */
  function info_playerInfo($pID, $force = FALSE)
  {
    if(!$this->info_playerList($force)) {
      return FALSE;
    }

    foreach($this->uList as $player) {
      if($player[1] == $pID) return $player;
    }

    return FALSE;
  }

  /**
   * Returns an image name for the specified player based on his status.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $pID
   * @param   boolean $force
   * @return  array
   */
  function info_playerImage($pID, $force = FALSE)
  {
    $player = $this->info_playerInfo($pID, $force);

    if(!$player) {
      return FALSE;
    }

    $pFlags = $this->info_translateFlag($player[13], CYTS_FLAG_PFLAG);

    if($pFlags["AW"]) {
      return "away";
    } elseif($pFlags["SM"]) {
      return "sndmuted";
    } elseif($pFlags["MM"]) {
      return "micmuted";
    } elseif($pFlags["CC"]) {
      return "chancmd";
    }

    return "player";
  }

  /**
   * Returns a list of channels on the virtual TeamSpeak server.
   * 
   * array:
   * [0], [unparsed] => Unparsed string
   * [1], [id]       => Channel ID
   * [2], [codec]    => Codec
   * [3], [parent]   => Parent channel ID
   * [4], [order]    => Order
   * [5], [maxusers] => Max Users
   * [6], [name]     => Name
   * [7], [flags]    => Flags
   * [8], [password] => Password
   * [9], [topic]    => Topic
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   boolean $force
   * @return  array
   */
  function info_channelList($force = FALSE)
  {
    if($force === FALSE && is_array($this->cList)) {
      return $this->cList;
    }

    $this->cList = $this->_fetchtable($this->_fastcall("cl"));

    if(is_array($this->cList)) {
      usort($this->cList, array(__CLASS__, "_sortchannels"));
    }

    return $this->cList;
  }

  /**
   * Returns the names of all channels on the virtual server.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   boolean $force
   * @return  array
   */
  function info_channelNameList($force = FALSE)
  {
    if(!$this->info_channelList($force)) {
      return array();
    }

    $nList = array();

    foreach($this->cList as $channel) {
      $nList[$channel[1]] = $channel[6];
    }

    return $nList;
  }

  /**
   * Returns the ID of the channel using the specified name.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   string  $cName
   * @param   boolean $force
   * @return  integer
   */
  function info_getChannelByName($cName, $force = FALSE)
  {
    if(!$this->info_channelList($force)) {
      return -1;
    }

    foreach($this->cList as $channel) {
      if($channel[6] == $cName) return $channel[1];
    }

    return -1;
  }

  /**
   * Returns channellist information about the specified channel.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $cID
   * @param   boolean $force
   * @return  array
   */
  function info_channelInfo($cID, $force = FALSE)
  {
    if(!$this->info_channelList($force)) {
      return FALSE;
    }

    foreach($this->cList as $channel) {
      if($channel[1] == $cID) return $channel;
    }

    return FALSE;
  }

  /**
   * Returns an array of all players in the specified channel.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $cID
   * @param   boolean $force
   * @return  array
   */
  function info_channelUser($cID, $force = FALSE)
  {
    if(!$this->info_playerList($force)) {
      return array();
    }

    $cUList = array();

    foreach($this->uList as $player) {
      if($player[2] == $cID) $cUList[] = $player;
    }

    return $cUList;
  }
  
  /**
   * Returns an array of all sub channels the specified channel.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $cID
   * @param   boolean $force
   * @return  array
   */
  function info_channelChannel($cID, $force = FALSE)
  {
    if(!$this->info_channelList($force)) {
      return array();
    }

    $cCList = array();

    foreach($this->cList as $channel) {
      if($channel[3] == $cID) $cCList[] = $channel;
    }

    return $cCList;
  }

  /**
   * Returns the name of a channel codec based on the codec identifier.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $cID
   * @return  string
   */
  function info_getCodec($cID)
  {
    $codecs = array(
      0x00 => "CELP 5.1 Kbit",
      0x01 => "CELP 6.3 Kbit",
      0x02 => "GSM 14.8 Kbit",
      0x03 => "GSM 16.4 Kbit",
      0x04 => "CELP Windows 5.2 Kbit",
      0x05 => "Speex 3.4 Kbit",
      0x06 => "Speex 5.2 Kbit",
      0x07 => "Speex 7.2 Kbit",
      0x08 => "Speex 9.3 Kbit",
      0x09 => "Speex 12.3 Kbit",
      0x0A => "Speex 16.3 Kbit",
      0x0B => "Speex 19.5 Kbit",
      0x0C => "Speex 25.9 Kbit",
    );

    return (isset($codecs[$cID])) ? $codecs[$cID] : "Unknown";
  }
  
  /**
   * Translates a given database date (i.e. 07122004162304140) into a timestamp.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer  $datetime
   * @return  integer
   */
  function info_translateDbDate($datetime)
  {    
    if(!is_numeric($datetime)) {
      return -1;
    }
    
    $day = substr($datetime, 0, 2);
    $month = substr($datetime, 2, 2);
    $year = substr($datetime, 4, 4);
    
    $hour = substr($datetime, 8, 2);
    $minute = substr($datetime, 10, 2);
    $second = substr($datetime, 12, 2);
    
    return strtotime($year . "-" . $month . "-" . $day . " " . $hour . ":" . $minute . ":" . $second);
  }

  /**
   * Translates given player flags to arrays.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   array   $player
   * @param   boolean $oStr
   * @return  string|array
   */
  function info_translatePlayerFlag($player, $oStr = FALSE)
  {
    $pFlags = array();

    $pFlags[] = (($player["pprivs"] & 4) == 4) ? "R" : "U";

    if(($player[12] & 1) == 1) $pFlags[] = "SA";
    if(($player[11] & 1) == 1) $pFlags[] = "CA";
    if(($player[11] & 8) == 8) $pFlags[] = "AO";
    if(($player[11] & 16) == 16) $pFlags[] = "AV";
    if(($player[11] & 2) == 2) $pFlags[] = "O";
    if(($player[11] & 4) == 4) $pFlags[] = "V";
    if(($player[12] & 16) == 16) $pFlags[] = "St";

    if($oStr) {
      return "(" . implode(" ", $pFlags) . ")";
    }

    return $pFlags;
  }

  /**
   * Translates given channel flags to arrays.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   array   $channel
   * @param   boolean $oStr
   * @return  string|array
   */
  function info_translateChannelFlag($channel, $oStr = FALSE)
  {
    $cFlags = array();

    $cFlags[] = (($channel["flags"] & 1) == 1) ? "U" : "R";

    if(($channel[7] & 2) == 2) $cFlags[] = "M";
    if(($channel[7] & 4) == 4) $cFlags[] = "P";
    if(($channel[7] & 8) == 8) $cFlags[] = "S";
    if(($channel[7] & 16) == 16) $cFlags[] = "D";

    if($oStr) {
      return "(" . implode("", $cFlags) . ")";
    }

    return $cFlags;
  }

  /**
   * Translates given flags to arrays.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $vFlag
   * @param   integer $fType
   * @param   boolean $oStr
   * @return  string|array
   */
  function info_translateFlag($vFlag, $fType = 0x01, $oStr = FALSE)
  {
    if($fType != 0x01 && $fType != 0x02 && $fType != 0x03 && $fType != 0x04 && $fType != 0x05) {
      return FALSE;
    }

    $decode = array(
      0x01 => array("CA", "O", "V", "AO", "AV"),
      0x02 => array("SA", "AR", "R", "", "ST"),
      0x03 => array("CC", "VR", "NW", "AW", "MM", "SM", "RC"),
      0x04 => array("U", "M", "P", "S", "D"),
      0x05 => array("UU", "RU", "UC", "RC"),
    );

    $uDec = $decode[$fType];
    $cFlags = array();
    $cnt = 0;

    while($vFlag > 0) {
      $nKey = $uDec[$cnt];
      $nVal = ($vFlag & 1 == 1) ? TRUE : FALSE;
      $cFlags[$nKey] = $nVal;
      $vFlag >>= 1;
      $cnt++;
    }

    foreach($decode[$fType] as $val) {
      if(!isset($cFlags[$val])) $cFlags[$val] = FALSE;
    }

    if(!$oStr) {
      return $cFlags;
    }

    $rFlag = array();

    if($fType == 0x04 && !$cFlags["U"]) {
      $rFlag[] = "R";
    }

    foreach($cFlags as $key => $val) {
      if($val) $rFlag[] = $key;
    }

    return implode(" ", $rFlag);
  }
  
  /**
   * Returns an array containing the names of the usergroups that can use the specified permission.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   string $name
   * @return  array
   */
  function info_translatePermClass($name)
  {    
    if(substr($name, 0, 6) == "Access") {
      return array("SA", "Registerred");
    } elseif(substr($name, 0, 5) == "Admin") {
      switch($name) {
        case "AdminMovePlayer":
          return array("SA", "Registerred", "Anonymous");
          break;
        case "AdminListDBServers":
        case "AdminAddServer":
        case "AdminDeleteServer":
        case "AdminEditServerIpPort":
        case "AdminEditServerMaxUsers":
          return array("Registerred");
          break;
        default:
          return array("SA", "Registerred");
          break;
      }
    } elseif(substr($name, 0, 7) == "Channel") {
      switch($name) {
        case "ChannelJoinRegisterred":
        case "ChannelCreateRegisterred":
        case "ChannelCreateUnregisterred":
        case "ChannelCreateDefault":
        case "ChannelCreateSubchannels":
        case "ChannelJoinChannelWithoutPassword":
          return array("SA", "Registerred", "Anonymous");
          break;
      }
    } elseif(substr($name, 0, 9) == "Privilege") {
      switch($name) {
        case "PrivilegeGrantAllowRegistration":
        case "PrivilegeRevokeAllowRegistration":
        case "PrivilegeRegisterSelfByDefault":
        case "PrivilegeAdminPlayerRegister":
        case "PrivilegePlayerDelete":
        case "PrivilegeGrantSA":
        case "PrivilegeRevokeSA":
          return array("SA", "Registerred", "Anonymous");
          break;
      }
    }
    
    return array("SA", "CA", "OP", "V", "Registerred", "Anonymous");
  }

  /**
   * Returns the ID of the servers default channel.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   boolean $force
   * @return  integer
   */
  function info_defaultChannel($force = FALSE)
  {
    if(!$this->info_channelList($force)) {
      return -1;
    }

    foreach($this->cList as $channel) {
      if(($channel[7] & 16) == 16) return $channel[1];
    }

    return -1;
  }

  /**
   * Returns a list of active virtual TeamSpeak server ports.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   boolean $force
   * @return  array
   */
  function info_serverList($force = FALSE)
  {
    if($force === FALSE && is_array($this->sList)) {
      return $this->sList;
    }

    $this->sList = $this->_fetchlist($this->_fastcall("sl"));

    if(is_array($this->sList)) {
      sort($this->sList);
    } else {
      return array();
    }
    
    return $this->sList;
  }

  /**
   * Returns an array containing detailed information about all virtual TeamSpeak servers.
   * 
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   boolean $force
   * @return  array
   */
  function info_extendServerList($force = FALSE)
  {
    if(!$this->info_serverList($force)) {
      return FALSE;
    }

    $sData = array();
    $sUDP = $this->udp;

    foreach($this->sList as $server) {
      if($this->select($server)) {
        $sData[$server] = $this->info_serverInfo();
      }
    }

    if($sUDP) {
      $this->select($sUDP);
    }

    return $sData;
  }
  
  /**
   * Returns an array containing detailed information about all online users on all virtual TeamSpeak servers.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   boolean $force
   * @return  array
   */
  function info_extendServerPlayerList($force = FALSE)
  {
    if(!$this->info_serverList($force)) {
      return FALSE;
    }

    $sData = array();
    $sUDP = $this->udp;
    
    foreach($this->sList as $server) {
      if($this->select($server)) {
        $sData[$server] = $this->info_playerList();
      }
    }
    
    if($sUDP) {
      $this->select($sUDP);
    }

    return $sData;
  }
  
  /**
   * Returns an array containing detailed information about all channels on all virtual TeamSpeak servers.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   boolean $force
   * @return  array
   */
  function info_extendServerChannelList($force = FALSE)
  {
    if(!$this->info_serverList($force)) {
      return FALSE;
    }

    $sData = array();
    $sUDP = $this->udp;
    
    foreach($this->sList as $server) {
      if($this->select($server)) {
        $sData[$server] = $this->info_channelList();
      }
    }
    
    if($sUDP) {
      $this->select($sUDP);
    }

    return $sData;
  }

  /**
   * Returns an array containing detailed information about the selected virtual server.
   * 
   * array:
   * [server_id]              => Server ID
   * [server_name]            => Server name
   * [server_platform]        => Server platform
   * [server_welcomemessage]  => Server welcome message
   * [server_webpost_linkurl] => Server WebPost linkurl
   * [server_webpost_posturl] => Server WebPost posturl
   * [server_password]        => Server password
   * [server_clan_server]     => Clan server
   * [server_udpport]         => UDP port
   * [server_maxusers]        => Slots
   * [server_packetssend]     => Packets sent by server
   * [server_bytessend]       => Bytes sent by server
   * [server_packetsreceived] => Packets received by server
   * [server_bytesreceived]   => Bytes received by server
   * [server_uptime]          => Server uptime in seconds
   * [server_currentusers]    => Connected players
   * [server_currentchannels] => Existing channels
   * [server_bwinlastsec]     => Incoming bandwith (last second)
   * [server_bwoutlastsec]    => Outgoing bandwith (last second)
   * [server_bwinlastmin]     => Incoming bandwith (last minute)
   * [server_bwoutlastmin]    => Outgoing bandwith (last minute)
   * [average_packet_loss]    => Average Packet Loss
   * 
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   boolean $force
   * @return  array
   */
  function info_serverInfo($force = FALSE)
  {
    if($force === FALSE && is_array($this->sInfo)) {
      return $this->sInfo;
    }
    
    $sInfo = $this->_fetchlist($this->_fastcall("si"));
    $sGapl = $this->_fetchlist($this->_fastcall("gapl"));

    if(!$sInfo || !$sGapl) {
      return FALSE;
    }

    $this->sInfo = array_merge($sInfo, $sGapl);

    return $this->sInfo;
  }
  
  /**
   * Returns the current number of online users on the selected server.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   boolean $force
   * @return  integer
   */
  function info_serverCurrentUsers($force = FALSE)
  {
    if($force === FALSE && is_array($this->sInfo)) {
      return $this->sInfo["server_currentusers"];
    }
    
    $this->info_serverInfo($force);
    
    return $this->sInfo["server_currentusers"];
  }

  /**
   * Returns the servers version information.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   boolean $force
   * @return  array
   */
  function info_serverVersion($force = FALSE)
  {
    if($force === FALSE && is_array($this->sVersion)) {
      return $this->sVersion;
    }

    $sRes = $this->_fastcall("ver");

    if(!is_array($sRes) || array_pop($sRes) != CYTS_OK) {
      return FALSE;
    }

    list($sVer, $sPlatform, $sLicense) = explode(" ", $sRes[0]);
    list($sVerMajor, $sVerMinor, $sRelease, $sBuild) = explode(".", $sVer);

    $this->sVersion = array(
      "unparsed" => $sRes[0],
      "total"    => $sVer,
      "major"    => $sVerMajor,
      "minor"    => $sVerMinor,
      "release"  => $sRelease,
      "build"    => $sBuild,
      "platform" => $sPlatform,
      "license"  => $sLicense,
    );

    return $this->sVersion;
  }

  /**
   * Returns an array containing detailed information about server instance.
   * 
   * [total_server_uptime]   => Server uptime
   * [total_server_version]  => Server version
   * [total_server_platform] => Server platform
   * [total_servers]         => Total virtual servers
   * [total_users_online]    => Total players online
   * [total_users_maximal]   => Total slots
   * [total_channels]        => Total channels
   * [total_bytesreceived]   => Total bytes received by server
   * [total_bytessend]       => Total bytes sent by server
   * [total_packetssend]     => Total packets sent by server
   * [total_packetsreceived] => Total bytes received by server
   * [total_bwoutlastmin]    => Total outgoing bandwith (last minute)
   * [total_bwoutlastsec]    => Total outgoing bandwith (last second)
   * [total_bwinlastmin]     => Total incoming bandwith (last minute)
   * [total_bwinlastsec]     => Total incoming bandwith (last second)
   * [isp_ispname]           => Server provider name<br />
   * [isp_linkurl]           => Server provider website
   * [isp_adminemail]        => Server provider email address
   * [isp_countrynumber]     => Server provider contry
   * 
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @return  array
   */
  function info_globalInfo()
  {
    $gInfo = $this->_fetchlist($this->_fastcall("gi"));

    if(!$gInfo) {
      return FALSE;
    }

    return $gInfo;
  }

  /**
   * Moves a specified player to the servers default channel.
   * 
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $pID
   * @return  boolean
   */
  function admin_kickFromChannel($pID)
  {
    if(!$cID = $this->info_defaultChannel()) {
      return FALSE;
    }

    return ($this->_extendcall("mptc " . $cID . " " . $pID) == CYTS_OK);
  }

  /**
   * Kicks a specified playerfrom the server.
   * 
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $pID
   * @param   string  $reason
   * @return  boolean
   */
  function admin_kick($pID, $reason = "")
  {
    return ($this->_extendcall("kick " . $pID . " " . $reason) == CYTS_OK);
  }

  /**
   * Closes the connection of a specified player.
   * 
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $pID
   * @return  boolean
   */
  function admin_remove($pID)
  {
    return ($this->_extendcall("removeclient " . $pID) == CYTS_OK);
  }

  /**
   * Moves a player into a specified channel.
   * 
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $pID
   * @param   integer $cID
   * @return  boolean
   */
  function admin_move($pID, $cID)
  {
    return ($this->_extendcall("mptc " . $cID . " " . $pID) == CYTS_OK);
  }

  /**
   * Moves, kicks or removes all players from a specified channel.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $cID
   * @param   integer $kMode
   * @param   integer|string $param
   * @return  boolean
   */
  function admin_clearChannel($cID, $kMode = 0x01, $param = null)
  {
    if(!$cUsers = $this->info_channelUser($cID)) {
      return FALSE;
    }

    if((!$param || !$this->info_channelInfo($param)) && $kMode == 0x01) {
      if(!$param = $this->info_defaultChannel()) return FALSE;
    }

    $cStats = TRUE;

    foreach($cUsers as $cUser) {
      if($kMode == 0x01) {
        $cStats = ($this->_extendcall("mptc " . $param . " " . $cUser[1]) == CYTS_OK) ? TRUE : FALSE;
      } elseif($kMode == 0x02) {
        $cStats = ($this->_extendcall("kick " . $cUser[1] . " " . $param) == CYTS_OK) ? TRUE : FALSE;
      } elseif($kMode == 0x03) {
        $cStats = ($this->_extendcall("removeclient " . $cUser[1]) == CYTS_OK) ? TRUE : FALSE;
      } else {
        $cStats = FALSE;
      }
    }

    return $cStats;
  }

  /**
   * Moves, kicks or removes all idlers from the virtual server.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $tIdle
   * @param   integer $tFlag
   * @param   integer $kMode
   * @param   integer|string $param
   * @return  boolean
   */
  function admin_kickIdler($tIdle, $tFlag, $kMode = 0x01, $param = null)
  {
    if(!$uList = $this->info_playerList()) {
      return FALSE;
    }
    
    if((!$param || !$this->info_channelInfo($param)) && $kMode == 0x01) {
      if(!$param = $this->info_defaultChannel()) return FALSE;
    }
    
    $mFlag = $this->info_translateFlag($tFlag, 0x05);
    $cStat = 0;
    
    foreach($uList as $user) {
      $cData = $this->info_channelInfo($user["c_id"]);    
      $cFlag = $this->info_translateFlag($cData["flags"], 0x04);
      $uFlag = $this->info_translateFlag($user["pprivs"], 0x02);
      
      if($cFlag["U"] && !$mFlag["UC"]) continue;
      if(!$cFlag["U"] && !$mFlag["RC"]) continue;            
      if($uFlag["R"] && !$mFlag["RU"]) continue;
      if(!$uFlag["R"] && !$mFlag["UU"]) continue;    
      if($user["idletime"] / 60 < $tIdle) continue;    
      
      if($kMode == 1) {
        if($this->admin_move($user["p_id"], $param)) $cStat++;
      } elseif ($kMode == 2) {
        if($this->admin_kick($user["p_id"], $param)) $cStat++;
      } elseif ($kMode == 3) {
        if($this->admin_remove($user["p_id"])) $cStat++;
      }
    }
    
    return $cStat;
  }

  /**
   * Returns a list of accounts on the virtual TeamSpeak server.
   * 
   * array:
   * [0], [unparsed]   => Unparsed string
   * [1], [id]         => Account ID
   * [2], [sa]         => Administrator
   * [3], [created]    => Creation date
   * [4], [lastonline] => Last login date
   * [5], [name]       => Account name
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   boolean $force
   * @return  array
   */
  function admin_dbUserList($force = FALSE)
  {
    if($force === FALSE && is_array($this->dbuList)) {
      return $this->dbuList;
    }

    $this->dbuList = $this->_fetchtable($this->_fastcall("dbuserlist"));

    return $this->dbuList;
  }
  
  /**
   * Returns a list of SA accounts on the virtual TeamSpeak server.
   * 
   * array:
   * [0], [unparsed]   => Unparsed string
   * [1], [id]         => Account ID
   * [2], [sa]         => Administrator
   * [3], [created]    => Creation date
   * [4], [lastonline] => Last login date
   * [5], [name]       => Account name
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   boolean $force
   * @return  array
   */
  function admin_dbAdminList($force = FALSE)
  {
    if(!$this->admin_dbUserList($force)) {
      return FALSE;
    }
    
    $admins = array();
    
    foreach($this->dbuList as $account) {
      if($account[2]) $admins[$account[1]] = $account;
    }
    
    return $admins;
  }

  /**
   * Returns the ID of the account using the specified loginname.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   string  $uName
   * @param   boolean $force
   * @return  integer
   */
  function admin_getDbUserByName($uName, $force = FALSE)
  {
    if(!$this->admin_dbUserList($force)) {
      return -1;
    }

    foreach($this->dbuList as $account) {
      if($account[5] == $uName) return $account[1];
    }

    return -1;
  }

  /**
   * Returns available information about a specified account.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $uID
   * @param   boolean $force
   * @return  array
   */
  function admin_getDbUserData($uID, $force = FALSE)
  {
    if(!$this->admin_dbUserList($force)) {
      return FALSE;
    }

    foreach($this->dbuList as $account) {
      if($account[1] == $uID) return $account;
    }

    return FALSE;
  }

  /**
   * Creates a new account.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   string  $lName
   * @param   string  $lPass
   * @param   boolean $lSA
   * @return  boolean
   */
  function admin_dbUserAdd($lName, $lPass, $lSA = FALSE)
  {
    $lSA = ($lSA) ? 1 : 0;

    return ($this->_extendcall("dbuseradd " . $lName . " " . $lPass . " " . $lPass . " " . $lSA) == CYTS_OK);
  }

  /**
   * Deletes an account.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $uID
   * @return  boolean
   */
  function admin_dbUserDel($uID)
  {
    return ($this->_extendcall("dbuserdel " . $uID) == CYTS_OK);
  }

  /**
   * Changes an account password.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $uID
   * @param   string  $uPW
   * @return  boolean
   */
  function admin_dbUserChangePW($uID, $uPW)
  {
    return ($this->_fastcall("dbuserchangepw " . $uID . " " . $uPW . " " . $uPW) == CYTS_OK);
  }

  /**
   * Changes admin permissions for an account.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $uID
   * @param   boolean $uSA
   * @return  boolean
   */
  function admin_dbUserChangeSA($uID, $uSA = FALSE)
  {
    $uSA = ($uSA) ? 1 : 0;

    return ($this->_extendcall("dbuserchangeattribs " . $uID . " " . $uSA) == CYTS_OK);
  }

  /**
   * Returns a list of SSA accounts on the TeamSpeak server.
   * 
   * array:
   * [0], [unparsed]   => Unparsed string
   * [1], [id]         => Account ID
   * [2], [created]    => Creation date
   * [3], [lastonline] => Last login date
   * [4], [name]       => Account name
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   boolean $force
   * @return  array
   */
  function sadmin_dbSUserList($force = FALSE)
  {
    if($force === FALSE && is_array($this->dbuList)) {
      return $this->dbsuList;
    }

    $this->dbsuList = $this->_fetchtable($this->_fastcall("dbsuserlist"));

    return $this->dbsuList;
  }

  /**
   * Returns the ID of the SSA account using the specified loginname.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   string  $uName
   * @param   boolean $force
   * @return  integer
   */
  function sadmin_getDbSUserByName($uName, $force = FALSE)
  {
    if(!$this->sadmin_dbSUserList($force)) {
      return -1;
    }

    foreach($this->dbsuList as $account) {
      if($account[4] == $uName) return $account[1];
    }

    return -1;
  }

  /**
   * Returns available information about a specified SSA account.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $uID
   * @param   boolean $force
   * @return  array
   */
  function sadmin_getDbSUserData($uID, $force = FALSE)
  {
    if(!$this->sadmin_dbSUserList($force)) {
      return FALSE;
    }

    foreach($this->dbsuList as $account) {
      if($account[1] == $uID) return $account;
    }

    return FALSE;
  }

  /**
   * Creates a new SSA account.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   string  $lName
   * @param   string  $lPass
   * @return  boolean
   */
  function sadmin_dbSUserAdd($lName, $lPass)
  {
    return ($this->_extendcall("dbsuseradd " . $lName . " " . $lPass . " " . $lPass) == CYTS_OK);
  }

  /**
   * Deletes an SSA account.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $uID
   * @return  boolean
   */
  function sadmin_dbSUserDel($uID)
  {
    return ($this->_extendcall("dbsuserdel " . $uID) == CYTS_OK);
  }

  /**
   * Changes an SSA account password.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $uID
   * @param   string  $uPW
   * @return  boolean
   */
  function sadmin_dbSUserChangePW($uID, $uPW)
  {
    return ($this->_fastcall("dbsuserchangepw " . $uID . " " . $uPW . " " . $uPW) == CYTS_OK);
  }

  /**
   * Sends a message to all clients on a virtual server.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   string  $sMsg
   * @param   boolean $uHide
   * @return  boolean
   */
  function sadmin_messageServer($sMsg, $uHide = FALSE)
  {
    $sMsg = ($uHide) ? "@" . $sMsg : $sMsg;

    return ($this->_fastcall("msg " . $sMsg) == CYTS_OK);
  }

  /**
   * Sends a message to a single player.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   string  $uID
   * @param   string  $sMsg
   * @param   boolean $uHide
   * @return  boolean
   */
  function sadmin_messageUser($uID, $sMsg, $uHide = FALSE)
  {
    $sMsg = ($uHide) ? "@" . $sMsg : $sMsg;

    return ($this->_fastcall("msgu " . $uID . " " . $sMsg) == CYTS_OK);
  }

  /**
   * Sends a message to all clients on all virtual servers.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   string  $sMsg
   * @param   boolean $uHide
   * @return  boolean
   */
  function sadmin_messageAll($sMsg, $uHide = FALSE)
  {
    $sMsg = ($uHide) ? "@" . $sMsg : $sMsg;

    return ($this->_fastcall("msgall " . $sMsg) == CYTS_OK);
  }

  /**
   * Finds channels by name based on a string.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   string $sQuery
   * @return  array
   */
  function sadmin_findChannel($sQuery)
  {
    return $this->_fetchtable($this->_fastcall("fc " . $sQuery));
  }

  /**
   * Finds players by nickname based on a string.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   string $sQuery
   * @return  array
   */
  function sadmin_findPlayer($sQuery)
  {
    return $this->_fetchtable($this->_fastcall("fp " . $sQuery));
  }

  /**
   * Returns detailed information about the channel and all players in it.
   * 
   * Output: 
   * [0] => Array (
   *    [0], [unparsed]       => Unparsed string
   *    [1], [c_id]           => Channel ID
   *    [2], [c_pid]          => Parent channel ID
   *    [3], [c_dbid]         => Channel database ID
   *    [4], [c_name]         => Channel name
   *    [5], [c_fU]           => Channel unregistered
   *    [6], [c_fM]           => Channel moderated
   *    [7], [c_fP]           => Channel password
   *    [8], [c_fH]           => Channel subchannels
   *    [9], [c_fD]           => Channel default
   *    [10], [c_codec]       => Channel codec
   *    [11], [c_order]       => Channel order
   *    [12], [c_maxusers]    => Channel max players
   *    [13], [c_created]     => Channel Creation Date/Time
   *    [14], [c_topic]       => Channel topic
   *    [15], [c_desctiption] => Channel description
   * )
   * [1, 2, 3, ...] => Array (
   *    [0], [unparsed] => Unparsed string
   *    [1], [p_id]     => Player ID
   *    [2], [p_nick]   => Nickname
   *    [3], [sa]       => Server admin
   *    [4], [ca]       => Channel admin
   *    [5], [o]        => Operator
   *    [6], [ao]       => Auto operator
   *    [7], [v]        => Voice
   *    [8], [av]       => Auto voice
   *    [9], [cst]      => Channel sticky
   *    [10], [reg]     => Registered
   * )
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $cID
   * @return  array
   */
  function sadmin_channelInfo($cID)
  {
    $sRes = $this->_fastcall("ci " . $cID);

    if(!is_array($sRes)) {
      return FALSE;
    }

    $cReply = array();
    $pReply = array();
    $cInfos = array();
    $sError = array_pop($sRes);

    if($sError != CYTS_OK && $sError != CYTS_INVALID_DATA) {
      return FALSE;
    }

    if($sError == CYTS_OK) {
      foreach($sRes as $key => $val) {
        if($val == "p_id\tp_nick\tsa\tca\to\tao\tv\tav\tcst\treg") {
          $cReply = array_slice($sRes, 0, $key-1);
          $pReply = array_slice($sRes, $key, count($sRes));
        }
      }
    } else {
      $cReply = $sRes;
    }

    $unparsed = implode("\n", $cReply);

    $cHead = explode("\t", array_shift($cReply));
    $cData = explode("\t", trim(implode("{cyts_nl}", $cReply)));

    array_unshift($cHead, "unparsed");
    array_unshift($cData, implode("\n", $cReply));

    foreach($cData as $key => $val) {
      if(substr($val, 0, 1) == "\"" && substr($val, -1) == "\"") {
        $val = substr($val, 1, strlen($val) - 2);
      }
      if($key == 15) {
        $val = str_replace("{cyts_nl}", "\n", $val);
      }
      $cInfos[0][$key] = $val;
      $cInfos[0][$cHead[$key]] = $val;
    }

    if(count($pReply)) {
      $pReply[] = CYTS_OK;
    }

    return array_merge($cInfos, $this->_fetchtable($pReply));
  }

  /**
   * Returns detailed information about the player and all of his channel permissions.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $cID
   * @return  array
   */
  function sadmin_playerInfo($pID)
  {
    $sRes = $this->_fastcall("pi " . $pID);

    if(!is_array($sRes)) {
      return FALSE;
    }

    $pReply = array();
    $cReply = array();
    $pInfos = array();
    $sError = array_pop($sRes);

    if($sError != CYTS_OK && $sError != CYTS_INVALID_DATA) {
      return FALSE;
    }

    if($sError == CYTS_OK) {
      foreach($sRes as $key => $val) {
        if($val == "c_id\tname\tca\to\tao\tv\tav") {
          $pReply = array_slice($sRes, 0, $key-1);
          $cReply = array_slice($sRes, $key, count($sRes));
        }
      }
    } else {
      $pReply = $sRes;
    }

    if(count($pReply)) $pReply[] = CYTS_OK;
    if(count($cReply)) $cReply[] = CYTS_OK;

    return array_merge($this->_fetchtable($pReply), $this->_fetchtable($cReply));
  }

  /**
   * Returns detailed information about the channel from the database including stored permissions.
   * 
   * Output: 
   * [0] => Array (
   *    [0], [unparsed]       => Unparsed string
   *    [1], [c_dbid]         => Channel database ID
   *    [2], [c_dbpid]        => Parent channel database ID
   *    [3], [c_name]         => Channel name
   *    [4], [c_fU]           => Channel unregistered
   *    [5], [c_fM]           => Channel moderated
   *    [6], [c_fP]           => Channel password
   *    [7], [c_fH]           => Channel subchannels
   *    [8], [c_fD]           => Channel default
   *    [9], [c_codec]        => Channel codec
   *    [10], [c_order]       => Channel order
   *    [11], [c_maxusers]    => Channel max players
   *    [12], [c_created]     => Channel creation date
   *    [13], [c_topic]       => Channel topic
   *    [14], [c_desctiption] => Channel description
   * )
   * [1, 2, 3, ...] => Array (
   *    [0], [unparsed]  => Unparsed string
   *    [1], [p_dbid]    => Player database ID
   *    [2], [loginname] => Loginname
   *    [3], [ca]        => Server admin
   *    [4], [ao]        => Channel admin
   *    [5], [av]        => Operator
   * )
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $cID
   * @return  array
   */
  function sadmin_dbChannelInfo($cID)
  {
    $sRes = $this->_fastcall("dbci " . $cID);

    if(!is_array($sRes)) {
      return FALSE;
    }

    $cReply = array();
    $pReply = array();
    $cInfos = array();
    $sError = array_pop($sRes);

    if($sError != CYTS_OK && $sError != CYTS_INVALID_DATA) {
      return FALSE;
    }

    if($sError == CYTS_OK) {
      foreach($sRes as $key => $val) {
        if($val == "p_dbid\tloginname\tca\tao\tav") {
          $cReply = array_slice($sRes, 0, $key-1);
          $pReply = array_slice($sRes, $key, count($sRes));
        }
      }
    } else {
      $cReply = $sRes;
    }

    $unparsed = implode("\n", $cReply);

    $cHead = explode("\t", array_shift($cReply));
    $cData = explode("\t", trim(implode("{cyts_nl}", $cReply)));

    array_unshift($cHead, "unparsed");
    array_unshift($cData, implode("\n", $cReply));

    foreach($cData as $key => $val) {
      if(substr($val, 0, 1) == "\"" && substr($val, -1) == "\"") {
        $val = substr($val, 1, strlen($val) - 2);
      }
      if($key == 14) {
        $val = str_replace("{cyts_nl}", "\n", $val);
      }
      $cInfos[0][$key] = $val;
      $cInfos[0][$cHead[$key]] = $val;
    }

    if(count($pReply)) {
      $pReply[] = CYTS_OK;
    }

    $pInfos = $this->_fetchtable($pReply);
    
    return array_merge($cInfos, is_array($pInfos) ? $pInfos : array());
  }

  /**
   * Finds accounts in the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   string $sQuery
   * @return  array
   */
  function sadmin_dbFindPlayer($sQuery)
  {
    return $this->_fetchtable($this->_fastcall("dbfp " . $sQuery));
  }

  /**
   * Returns a list of virtual servers in the TeamSpeak instance.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   boolean $force
   * @return  array
   */
  function sadmin_dbServerList($force = FALSE)
  {
    if($force === FALSE && is_array($this->dbsList)) {
      return $this->dbsList;
    }

    $this->dbsList = $this->_fetchtable($this->_fastcall("dbserverlist"));

    return $this->dbsList;
  }
  
  /**
   * Returns a list of all virtual server ports on the TeamSpeak host.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   array   $sList
   * @param   boolean $force
   * @return  array
   */
  function sadmin_dbServerPorts($sList = null, $force = FALSE)
  {
    if(!is_array($sList) && !$this->sadmin_dbServerList($force)) {
      return FALSE;
    }
    
    $servers = array();
    $dbsList = (is_array($sList)) ? $sList : $this->dbsList;

    foreach($dbsList as $server) {
      $servers[$server[1]] = $server[2]; 
    }
    
    sort($servers);
    
    return $servers;
  }

  /**
   * Returns dbserverlist information about the specified server.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $sID
   * @param   array   $sList
   * @param   boolean $force
   * @return  array
   */
  function sadmin_dbServerInfo($sID = null, $sList = null, $force = FALSE)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    if(!is_array($sList) && !$this->sadmin_dbServerList($force)) {
      return FALSE;
    }

    $dbsList = (is_array($sList)) ? $sList : $this->dbsList;

    foreach($dbsList as $server) {
      if($server[1] == $sID) return $server;
    }

    return FALSE;
  }
  
  /**
   * Returns dbserverlist information about the specified server.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $sUDP
   * @param   array   $sList
   * @param   boolean $force
   * @return  array
   */
  function sadmin_dbServerInfoByPort($sUDP = null, $sList = null, $force = FALSE)
  {
    $sUDP = ($sUDP) ? $sUDP : $this->udp;
    
    if(!$sUDP) {
      $this->_debug(null, "Missing virtual server port", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    if(!is_array($sList) && !$this->sadmin_dbServerList($force)) {
      return FALSE;
    }

    $dbsList = (is_array($sList)) ? $sList : $this->dbsList;

    foreach($dbsList as $server) {
      if($server[2] == $sUDP) return $server;
    }

    return FALSE;
  }
  
  /**
   * Returns the database ID of the specified server.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $sUDP
   * @param   array   $sList
   * @param   boolean $force
   * @return  integer
   */
  function sadmin_dbServerIdByPort($sUDP = null, $sList = null, $force = FALSE)
  {
    $sUDP = ($sUDP) ? $sUDP : $this->sid;
    
    if(!$sUDP) {
      $this->_debug(null, "Missing virtual server port", CYTS_DEBUG_ERROR);
      return -1;
    }
    
    if(!is_array($sList) && !$this->sadmin_dbServerList($force)) {
      return -1;
    }

    $dbsList = (is_array($sList)) ? $sList : $this->dbsList;

    foreach($dbsList as $server) {
      if($server[2] == $sUDP) return $server[1];
    }

    return -1;
  }
  
  /**
   * Returns the UDP port of the specified server.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $sID
   * @param   array   $sList
   * @param   boolean $force
   * @return  integer
   */
  function sadmin_dbServerPortById($sID = null, $sList = null, $force = FALSE)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return -1;
    }
    
    if(!is_array($sList) && !$this->sadmin_dbServerList($force)) {
      return -1;
    }

    $dbsList = (is_array($sList)) ? $sList : $this->dbsList;

    foreach($dbsList as $server) {
      if($server[1] == $sID) return $server[2];
    }

    return -1;
  }
  
  /**
   * Returns the status of a virtual server.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $sID
   * @param   boolean $force
   * @return  integer
   */
  function sadmin_serverStatus($sID = null, $force = FALSE)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $server = $this->sadmin_dbServerInfo($sID, null, $force);
    
    if(!is_array($server)) {
      $this->_debug(null, "Invalid virtual server ID (" .  $sID . ")", CYTS_DEBUG_ERROR);
      return 0;
    }
    
    return intval($server["status"]);
  }

  /**
   * Starts a virtual server.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $sID
   * @return  boolean
   */
  function sadmin_serverStart($sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    return ($this->_extendcall("serverstart " . $sID) == CYTS_OK);
  }

  /**
   * Stops a virtual server.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $sID
   * @return  boolean
   */
  function sadmin_serverStop($sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
       
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $sUDP = $this->sadmin_dbServerPortById($sID);
    
    if($this->sid && $sID != $this->sid) {
      $this->fastcall("sel " . $sUDP);
    }
    
    if($this->_extendcall("serverstop " . $sUDP) != CYTS_OK) {
      $this->_readline(); // ERROR, unable to initialize server
      return FALSE;
    }
    
    if($sID == $this->sid) {      
      $this->sid = null;
      $this->udp = null;
      
      $this->_debug(null, "Virtual server port deselected", CYTS_DEBUG_WARN);
    }
    
    if($this->sid && $sID != $this->sid) {
      $this->fastcall("sel " . $this->udp);
    }
    
    return TRUE;
  }
  
  /**
   * Restarts a virtual server.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $sID
   * @return  boolean
   */
  function sadmin_serverRestart($sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    if(!$this->sadmin_serverStop($sID)) return FALSE;
    if(!$this->sadmin_serverStart($sID)) return FALSE;
    
    return ($sID == $this->sid) ? $this->select($this->udp) : TRUE;
  }

  /**
   * Deletes the specified virtual server.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $sID
   * @return  boolean
   */
  function sadmin_serverDelete($sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    return ($this->_extendcall("serverdel " . $sID) == CYTS_OK);
  }

  /**
   * Creates a virtual server using the specified UDP port.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $sUDP
   * @return  boolean
   */
  function sadmin_serverAdd($sUDP)
  {
    return ($this->_extendcall("serveradd " . $sUDP) == CYTS_OK);
  }

  /**
   * Changes a virtual servers settings.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   string $sVar
   * @param   string $sVal
   * @return  boolean
   */
  function sadmin_serverSet($sVar, $sVal)
  {
    return ($this->_extendcall("serverset " . $sVar . " " . $sVal) == CYTS_OK);
  }

  /**
   * Returns a specified number of lines from the servers logfile.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $lNum
   * @param   boolean $fetch
   * @return  array
   */
  function sadmin_logRead($lNum = 30, $fetch = FALSE)
  {
    $sLog = $this->_fastcall("log " . $lNum);
    
    if(!is_array($sLog)) {
      return ($sLog == CYTS_OK) ? array() : FALSE;
    } elseif(array_pop($sLog) != CYTS_OK) {
      return FALSE;
    }

    return ($fetch) ? $sLog : $this->_fetchlog($sLog);
  }

  /**
   * Returns a specified number of lines from the servers logfile matching the given pattern.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   string  $lQuery
   * @param   boolean $fetch
   * @return  array
   */
  function sadmin_logFind($lQuery, $fetch = FALSE)
  {
    $sLog = $this->_fastcall("logfind " . $lQuery);

    if(!is_array($sLog)) {
      return ($sLog == CYTS_OK) ? array() : FALSE;
    } elseif(array_pop($sLog) != CYTS_OK) {
      return FALSE;
    }

    return ($fetch) ? $sLog : $this->_fetchlog($sLog);
  }

  /**
   * Writes a line to the servers logfile.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   string $lLine
   * @return  boolean
   */
  function sadmin_logWrite($lLine)
  {
    return ($this->_fastcall("logmark " . $lLine) == CYTS_OK);
  }

  /**
   * Bans the IP address of a specified player.
   * 
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $pID
   * @param   integer $bTime
   * @return  boolean
   */
  function sadmin_banPlayer($pID, $bTime = 0)
  {
    $sRes = ($this->_fastcall("banplayer " . $pID . " " . $bTime) == CYTS_OK);

    return $sRes;
  }

  /**
   * Bans a specified IP address.
   * 
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $pIP
   * @param   integer $bTime
   * @return  boolean
   */
  function sadmin_banIp($pIP, $bTime = 0)
  {
    if(long2ip(ip2long($pIP)) != $pIP) {
      return FALSE;
    }

    $sRes = ($this->_fastcall("banadd " . $pIP . " " . $bTime) == CYTS_OK);

    return $sRes;
  }
  
  /**
   * Bans a specified target address including wildcards.
   * 
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $bTarget
   * @param   integer $bTime
   * @return  boolean
   */
  function sadmin_banAdd($bTarget, $bTime = 0)
  {
    if(!preg_match("/^[\d\*]{1,3}\.[\d\*]{1,3}\.[\d\*]{1,3}\.[\d\*]{1,3}$/", $bTarget)) {
      return FALSE;
    }
    
    $sRes = ($this->_fastcall("banadd " . $pIP . " " . $bTime) == CYTS_OK);

    return $sRes;
  }

  /**
   * Deletes an entry from the banlist.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $bID
   * @return  boolean
   */
  function sadmin_banDel($bID)
  {
    $sRes = ($this->_fastcall("bandel " . $bID) == CYTS_OK);

    return $sRes;
  }

  /**
   * Resets the banlist.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @return  boolean
   */
  function sadmin_banClear()
  {
    $sRes = ($this->_fastcall("banclear") == CYTS_OK);

    return $sRes;
  }

  /**
   * Returns the virtual servers banlist.
   * 
   * array:
   * [0], [unparsed] => Unparsed string
   * [1], [b_id]     => Ban ID
   * [2], [ip]       => Ip address
   * [3], [created]  => Ban creation date
   * [4], [mins]     => Minutes
   * [5], [by]       => Invoker information
   * 
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @return  array
   */
  function sadmin_banList()
  {
    if($force === FALSE && is_array($this->bList)) {
      return $this->bList;
    }

    $sRes = $this->_fastcall("banlist");
    
    if($sRes == CYTS_INVALID_DATA) {
      return array();
    }

    return $this->_fetchtable($sRes);
  }

  /**
   * Changes a players privileges.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $pID
   * @param   string  $sPriv
   * @param   integer $pVal
   * @return  boolean
   */
  function sadmin_sppriv($pID, $sPriv, $pVal)
  {
    return ($this->_extendcall("sppriv " . $pID . " " . $sPriv . " " . $pVal) == CYTS_OK);
  }

  /**
   * Executes one or more commands on the server.
   * 
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   string|array $sCall
   * @return  string|array
   */
  function fastcall($sCall)
  {
    return $this->_fastcall($sCall);
  }
  
  /**
   * Executes one SQL command on the server.
   * 
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   string $sCall
   * @return  string|array
   */
  function sqlcall($sCall)
  {
    return $this->_sqlcall($sCall);
  }

  /**
   * Executes one or more commands on the server and resets the channel and playerlists.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   string|array $sCall
   * @return  string|array
   */
  function extendcall($sCall)
  {
    return $this->_extendcall($sCall);
  }

  /**
   * Returns an array filled with debug information.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @return  array
   */
  function debug()
  {
    return $this->debug;
  }
  
  /**
   * Returns the last reply stored in the debug array.
   * 
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @return  string
   */
  function debug_lastreply()
  {
    return $this->debug[count($this->debug)-1]["rpl"];
  }
  
  /**
   * Returns the servers settings in a array which is equal to the output of wi_readServerSettings().
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $sID
   * @return  array
   */
  function sql_readServerSettings($sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $sRes = $this->_fetchsqllist($this->_sqlcall("SELECT * FROM ts2_servers WHERE i_server_id = " . intval($sID)));
    
    if(!is_array($sRes)) {
      return FALSE;
    }
        
    return array(
      "servername"           => $sRes["s_server_name"],
      "serverudpport"        => $sRes["i_server_udpport"],
      "servermaxusers"       => $sRes["i_server_maxusers"],
      "serverpassword"       => $sRes["s_server_password"],
      "serverwelcomemessage" => $sRes["s_server_welcomemessage"],
      "CODECCelp51"          => ($sRes["b_server_allow_codec_celp51"] == -1 ? 1 : 0),
      "CODECCelp63"          => ($sRes["b_server_allow_codec_celp63"] == -1 ? 1 : 0),
      "CODECGSM148"          => ($sRes["b_server_allow_codec_gsm148"] == -1 ? 1 : 0),
      "CODECGSM164"          => ($sRes["b_server_allow_codec_gsm164"] == -1 ? 1 : 0),
      "CODECWindowsCELP52"   => ($sRes["b_server_allow_codec_celp52"] == -1 ? 1 : 0),
      "CODECSPEEX2150"       => ($sRes["b_server_allow_codec_speex2150"] == -1 ? 1 : 0),
      "CODECSPEEX3950"       => ($sRes["b_server_allow_codec_speex3950"] == -1 ? 1 : 0),
      "CODECSPEEX5950"       => ($sRes["b_server_allow_codec_speex5950"] == -1 ? 1 : 0),
      "CODECSPEEX8000"       => ($sRes["b_server_allow_codec_speex8000"] == -1 ? 1 : 0),
      "CODECSPEEX11000"      => ($sRes["b_server_allow_codec_speex11000"] == -1 ? 1 : 0),
      "CODECSPEEX15000"      => ($sRes["b_server_allow_codec_speex15000"] == -1 ? 1 : 0),
      "CODECSPEEX18200"      => ($sRes["b_server_allow_codec_speex18200"] == -1 ? 1 : 0),
      "CODECSPEEX24600"      => ($sRes["b_server_allow_codec_speex24600"] == -1 ? 1 : 0),
      "serverwebpostposturl" => $sRes["s_server_webposturl"],
      "serverwebpostlinkurl" => $sRes["s_server_weblinkurl"],
      "servertype"           => ($sRes["b_server_clan_server"] == "-1" ? 1 : 2)
    );
  }
  
  /**
   * Modifies a servers settings in the database, but the virtual server needs to be restarted to apply the changes.
   * 
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   array $sData
   * @param   array $sID
   * @return  boolean
   */
  function sql_writeServerSettings($sData, $sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    if(!is_array($sData)) {
      return FALSE;
    }
    
    $fields = array();
    $sQuery = array();
    
    $webStrKeys = array("servername", "serverudpport", "servermaxusers", "serverpassword", "serverwelcomemessage", "serverwebpostposturl", "serverwebpostlinkurl");
    $sqlStrKeys = array("s_server_name", "i_server_udpport", "i_server_maxusers", "s_server_password", "s_server_welcomemessage", "s_server_webposturl", "s_server_weblinkurl");
    
    foreach($webStrKeys as $key => $val) {
      if(array_key_exists($val, $sData)) $fields[$sqlStrKeys[$key]] = addcslashes($sData[$val], "\"");
    }
    
    $webCodecKeys = array("CODECCelp51", "CODECCelp63", "CODECGSM148", "CODECGSM164", "CODECWindowsCELP52", "CODECSPEEX2150", "CODECSPEEX3950", "CODECSPEEX5950", "CODECSPEEX8000", "CODECSPEEX11000", "CODECSPEEX15000", "CODECSPEEX18200", "CODECSPEEX24600");
    $sqlCodecKeys = array("b_server_allow_codec_celp51", "b_server_allow_codec_celp63", "b_server_allow_codec_gsm148", "b_server_allow_codec_gsm164", "b_server_allow_codec_celp52", "b_server_allow_codec_speex2150", "b_server_allow_codec_speex3950", "b_server_allow_codec_speex5950", "b_server_allow_codec_speex8000", "b_server_allow_codec_speex11000", "b_server_allow_codec_speex15000", "b_server_allow_codec_speex18200", "b_server_allow_codec_speex24600");
    
    foreach($webCodecKeys as $key => $val) {
      if(array_key_exists($val, $sData)) $fields[$sqlCodecKeys[$key]] = ($sData[$val] ? -1 : 0);
    }
    
    if(array_key_exists("servertype", $sData)) {
      $fields["b_server_clan_server"] = ($sData["servertype"] == 1) ? -1 : 0;
    }
    
    foreach($fields as $key => $val) {
      $sQuery[] = $key . " = \"" . $val . "\"";
    }
        
    return ($this->_sqlcall("UPDATE ts2_servers SET " . implode($sQuery, ", ") . " WHERE i_server_id = " . intval($sID)) == CYTS_OK);
  }
  
  /**
   * Returns the servers group permission settings in a array which is equal to the output of wi_readServerSettings().
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @todo    Filter global permissions from channel groups
   * @param   string  $class
   * @param   integer $sID
   * @return  array
   */
  function sql_readGroupPermissions($class, $sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    if($class == "r") {
      $class = "Registerred";
    } elseif($class == "u") {
      $class = "Anonymous";
    } else {
      $class = strtoupper($class);
    }
    
    $classes = array("SA", "CA", "OP", "V", "Registerred", "Anonymous");
    
    if(!in_array($class, $classes, TRUE)) {
      $this->_debug(null, "Invalid group identifier", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $sRes = $this->_fetchsqllist($this->_sqlcall("SELECT * FROM ts2_server_privileges WHERE i_sp_server_id = " . intval($sID) . " AND s_sp_usergroup = \"_ug" . $class . "\""));
    
    if(!is_array($sRes)) {
      return FALSE;
    }
    
    foreach($sRes as $key => $val) {
      unset($sRes[$key]);
      if(strstr($key, "b_sp_") !== FALSE) {
        $sRes["ug" . $class . "_up" . substr($key, 5)] = ($val == -1 ? 1 : 0);
      }
    }
    
    return $sRes;
  }
  
  /**
   * Modifies a servers group permission settings in the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @todo    Filter global permissions from channel groups
   * @param   string  $class
   * @param   array   $sData
   * @param   integer $sID
   * @return  boolean
   */
  function sql_writeGroupPermissions($class, $sData = array(), $sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    if($class == "r") {
      $class = "Registerred";
    } elseif($class == "u") {
      $class = "Anonymous";
    } else {
      $class = strtoupper($class);
    }
    
    $classes = array("SA", "CA", "OP", "V", "Registerred", "Anonymous");
    
    if(!in_array($class, $classes, TRUE)) {
      $this->_debug(null, "Invalid group identifier", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    foreach($sData as $key => $val) {
      if(strstr($key, "ug" . $class . "_up") !== FALSE) {
        $sQuery[] = "b_sp_" . str_replace("ug" . $class . "_up", "", $key) . " = \"" . ($val == 1 ? -1 : 0) . "\"";
      }
    }
    
    if($sRes = ($this->_sqlcall("UPDATE ts2_server_privileges SET " . implode($sQuery, ", ") . " WHERE i_sp_server_id = " . intval($sID) . " AND s_sp_usergroup = \"_ug" . $class . "\"") == CYTS_OK)) {
      $this->sql_rehash();
    }
    
    return $sRes;
  }
  
  /**
   * Returns a list of server groups from the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @return  array
   */
  function sql_groupList($sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $sRes = $this->_fetchsqltable($this->_sqlcall("SELECT * FROM ts2_server_privileges WHERE i_sp_server_id = " . intval($sID)));
    
    if(!is_array($sRes)) {
      return FALSE;
    }
        
    return $sRes;
  }
  
  /**
   * Returns all permissions the specified usergroup can use including their current values.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   string  $class
   * @param   integer $sID
   * @return  array
   */
  function sql_groupInfo($class, $sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    if($class == "r") {
      $class = "Registerred";
    } elseif($class == "u") {
      $class = "Anonymous";
    } else {
      $class = strtoupper($class);
    }
    
    $classes = array("SA", "CA", "OP", "V", "Registerred", "Anonymous");
    
    if(!in_array($class, $classes, TRUE)) {
      $this->_debug(null, "Invalid group identifier", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $sRes = $this->_fetchsqllist($this->_sqlcall("SELECT * FROM ts2_server_privileges WHERE i_sp_server_id = " . intval($sID) . " AND s_sp_usergroup = \"_ug" . $class . "\""));
    
    if(!is_array($sRes)) {
      return FALSE;
    }
    
    foreach($sRes as $key => $val) {
      unset($sRes[$key]);
      if(strstr($key, "b_sp_") !== FALSE) {
        if(in_array($class, $this->info_translatePermClass(substr($key, 5)))) $sRes[substr($key, 5)] = ($val == -1 ? 1 : 0);
      }
    }
    
    return $sRes;
  }
  
  /**
   * Returns a list of channels from the servers database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @todo    Parse channel descriptions
   * @param   integer $sID
   * @return  array
   */
  function sql_dbChannelList($sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $columns = array(
      "i_channel_id",
      "i_channel_codec",
      "i_channel_server_id",
      "i_channel_parent_id",
      "i_channel_order",
      "i_channel_codec",
      "i_channel_maxusers",
      "s_channel_name",
      "b_channel_flag_default",
      "b_channel_flag_hierarchical",
      "b_channel_flag_moderated",
      "s_channel_password",
      "s_channel_topic",
      "dt_channel_created",
    );
    
    $sRes = $this->_fetchsqltable($this->_sqlcall("SELECT " . implode(", ", $columns) . " FROM ts2_channels WHERE i_channel_server_id = " . intval($sID) . " ORDER BY i_channel_order, s_channel_name"));
    
    if(!is_array($sRes)) {
      return FALSE;
    }
    
    for($i = 0; $i < count($sRes); $i++) {
      $sRes[$i][3] = $this->info_translateDbDate($sRes[$i]["dt_channel_created"]);
      $sRes[$i]["dt_channel_created"] = $sRes[$i][3];
      $sRes[$i][12] = $this->info_getCodec($sRes[$i]["i_channel_codec"]);
      $sRes[$i]["s_channel_codec_name"] = $sRes[$i][12];
    }
        
    return $sRes;
  }
  
  /**
   * Returns information about a specified channel from the servers database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $sID
   * @param   integer $sID
   * @return  array
   */
  function sql_dbChannelInfo($cID, $sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $columns = array(
      "i_channel_id",
      "i_channel_codec",
      "i_channel_server_id",
      "i_channel_parent_id",
      "i_channel_order",
      "i_channel_codec",
      "i_channel_maxusers",
      "s_channel_name",
      "b_channel_flag_default",
      "b_channel_flag_hierarchical",
      "b_channel_flag_moderated",
      "s_channel_password",
      "s_channel_topic",
      "dt_channel_created",
    );
    
    $sRes = $this->_fetchsqllist($this->_sqlcall("SELECT " . implode(", ", $columns) . " FROM ts2_channels WHERE i_channel_server_id = " . intval($sID) . " AND i_channel_id = " . intval($cID)));
    
    if(!is_array($sRes)) {
      return FALSE;
    }
    
    $sRes["dt_channel_created"] = $this->info_translateDbDate($sRes["dt_channel_created"]);
    $sRes["s_channel_codec_name"] = $this->info_getCodec($sRes["i_channel_codec"]);
    $sRes["s_channel_description"] = $this->sql_dbChannelDescription($cID, $sID);
        
    return $sRes;
  }
  
  /**
   * Returns the description of a specified channel from the servers database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $sID
   * @param   integer $sID
   * @return  string
   */
  function sql_dbChannelDescription($cID, $sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $sRes = $this->_sqlcall("SELECT s_channel_description FROM ts2_channels WHERE i_channel_server_id = " . intval($sID) . " AND i_channel_id = " . intval($cID));
    
    if(!is_array($sRes) || array_pop($sRes) != CYTS_OK) {
      return FALSE;
    }
    
    $sRes = substr(implode("{cyts_nl}", $sRes), 23, -1);
    
    return trim(str_replace("{cyts_nl}", "\n", $sRes));
  }
  
  /**
   * Returns the number of registered channels on a virtual server.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $sID
   * @return  integer
   */
  function sql_dbChannelCount($sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $sRes = $this->_fetchsqllist($this->_sqlcall("SELECT COUNT(*) as i_channel_count FROM ts2_channels WHERE i_channel_server_id = " . intval($sID)));
    
    if(!is_array($sRes)) {
      return FALSE;
    }
    
    return intval($sRes["i_channel_count"]);
  }
  
  /**
   * Creates a new channel in the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   string  $cName
   * @param   array   $cData
   * @param   integer $sID
   * @return  boolean
   */
  function sql_dbChannelAdd($cName, $cData = array(), $sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    if($this->sql_dbVerifyChannelname($cName, $sID)) {
      $this->_debug(null, "Channelname already in use", CYTS_DEBUG_ERROR);
      return FALSE;
    }

    $this->_debug(null, __CLASS__ . "::" . __FUNCTION__ . "() is currently disabled due to internal testing", CYTS_DEBUG_WARN);
    
    return FALSE;
  }
  
  /**
   * Modifies a channel in the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $cID
   * @param   array   $pData
   * @param   integer $sID
   * @return  boolean
   */
  function sql_dbChannelEdit($cID, $cData = array(), $sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $this->_debug(null, __CLASS__ . "::" . __FUNCTION__ . "() is currently disabled due to internal testing", CYTS_DEBUG_WARN);
    
    return FALSE;
  }
    
  /**
   * Returns information about the server from the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $sID
   * @return  array
   */
  function sql_serverInfo($sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $sRes = $this->_fetchsqllist($this->_sqlcall("SELECT * FROM ts2_servers WHERE i_server_id = " . intval($sID)));
    
    if(!is_array($sRes)) {
      return FALSE;
    }
    
    $sRes["b_server_active"] = ($this->sadmin_serverStatus($sRes["i_server_id"]) == 1) ? -1 : 0;
    $sRes["dt_server_created"] = $this->info_translateDbDate($sRes["dt_server_created"]);
        
    return $sRes;
  }
  
  /**
   * Returns a list of virtual servers from the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @return  array
   */
  function sql_serverList($limit = null, $offset = null)
  {
    $limit = ($limit) ? " LIMIT " . $limit : "";
    
    if($limit) {
      $offset = ($offset) ? " OFFSET " . $offset : "";
    } else {
      $offset = "";
    }
    
    $sRes = $this->_fetchsqltable($this->_sqlcall("SELECT * FROM ts2_servers" . $limit . $offset));
    
    if(!is_array($sRes)) {
      return FALSE;
    }
    
    for($i = 0; $i < count($sRes); $i++) {
      $sRes[$i]["b_server_active"] = ($this->sadmin_serverStatus($sRes[$i]["i_server_id"]) == 1) ? -1 : 0;
      $sRes[$i][15] = $this->info_translateDbDate($sRes[$i]["dt_server_created"]);
      $sRes[$i]["dt_server_created"] = $sRes[$i][15];
    }
        
    return $sRes;
  }
  
  /**
   * Returns a servers description from the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $sID
   * @param   boolean $force
   * @return  string
   */
  function sql_getServerDescription($sID = null, $force = FALSE)
  {
    if($force === FALSE && $this->sDescription !== null) {
      return $this->sDescription;
    }
    
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $sRes = $this->_fetchsqllist($this->_sqlcall("SELECT s_server_description FROM ts2_servers WHERE i_server_id = " . intval($sID)));
    
    if(!is_array($sRes)) {
      return FALSE;
    }
    
    $this->sDescription = $sRes["s_server_description"];
    
    return $sRes["s_server_description"];
  }
  
  /**
   * Updates a servers description in the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   string  $sDescription
   * @param   integer $sID
   * @return  boolean
   */
  function sql_setServerDescription($sDescription, $sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    return ($this->_sqlcall("UPDATE ts2_servers SET s_server_description = \"" . nl2br(addcslashes($sDescription, "\"")) . "\" WHERE i_server_id = " . intval($sID)) == CYTS_OK);
  }
  
  /**
   * Returns the number of virtual servers in the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @return  integer
   */
  function sql_serverCount()
  {
    $sRes = $this->_fetchsqllist($this->_sqlcall("SELECT COUNT(*) as i_server_count FROM ts2_servers"));
    
    if(!is_array($sRes)) {
      return FALSE;
    }
    
    return intval($sRes["i_server_count"]);
  }
  
  /**
   * Returns a list of accounts on a virtual server from the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $limit
   * @param   integer $offset
   * @param   integer $sID
   * @return  array
   */
  function sql_dbUserList($limit = null, $offset = null, $sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $limit = ($limit) ? " LIMIT " . $limit : "";
    
    if($limit) {
      $offset = ($offset) ? " OFFSET " . $offset : "";
    } else {
      $offset = "";
    }
    
    $sRes = $this->_fetchsqltable($this->_sqlcall("SELECT * FROM ts2_clients WHERE i_client_server_id = " . intval($sID) . $limit . $offset));
    
    for($i = 0; $i < count($sRes); $i++) {
      $sRes[$i][1] = $this->info_translateDbDate($sRes[$i]["dt_client_created"]);
      $sRes[$i][2] = $this->info_translateDbDate($sRes[$i]["dt_client_lastonline"]);
      $sRes[$i]["dt_client_created"] = $sRes[$i][1];
      $sRes[$i]["dt_client_lastonline"] = $sRes[$i][2];
    }
    
    return $sRes;
  }
  
  /**
   * Returns information about a registered user on a virtual server from the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $uID
   * @param   integer $sID
   * @return  array
   */
  function sql_dbUserInfo($uID, $sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $sRes = $this->_fetchsqllist($this->_sqlcall("SELECT * FROM ts2_clients WHERE i_client_server_id = " . intval($sID) . " AND i_client_id = " . intval($uID)));
    
    if(!is_array($sRes)) {
      return FALSE;
    }
    
    $sRes["dt_client_created"] = $this->info_translateDbDate($sRes["dt_client_created"]);
    $sRes["dt_client_lastonline"] = $this->info_translateDbDate($sRes["dt_client_lastonline"]);
    
    return $sRes;
  }
  
  /**
   * Returns the number of user accounts on a virtual server.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $sID
   * @return  integer
   */
  function sql_dbUserCount($sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $sRes = $this->_fetchsqllist($this->_sqlcall("SELECT COUNT(*) as i_client_count FROM ts2_clients WHERE i_client_server_id = " . intval($sID)));
    
    if(!is_array($sRes)) {
      return FALSE;
    }
    
    return intval($sRes["i_client_count"]);
  }
  
  /**
   * Returns a list of accounts on a virtual server matching a given pattern from the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   string  $pattern
   * @param   integer $sID
   * @return  array
   */
  function sql_dbFindPlayer($sQuery, $sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $sQueries = preg_split("/\s+/", trim($sQuery));
    $patterns = array();
    
    foreach($sQueries as $pattern) {
      $pattern = str_replace("\"", "'", $pattern); // workaround for sql command bug
      $patterns[] = "s_client_name LIKE \"%" . addcslashes($pattern, "\"") . "%\"";
    }
    
    $sRes = $this->_fetchsqltable($this->_sqlcall("SELECT * FROM ts2_clients WHERE i_client_server_id = " . intval($sID) . " AND (" . implode(' OR ', $patterns) . ")"));
    
    for($i = 0; $i < count($sRes); $i++) {
      $sRes[$i][1] = $this->info_translateDbDate($sRes[$i]["dt_client_created"]);
      $sRes[$i][2] = $this->info_translateDbDate($sRes[$i]["dt_client_lastonline"]);
      $sRes[$i]["dt_client_created"] = $sRes[$i][1];
      $sRes[$i]["dt_client_lastonline"] = $sRes[$i][2];
    }
    
    return $sRes;
  }
  
  /**
   * Returns channel privs of a registered user on a virtual server from the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $uID
   * @param   integer $sID
   * @return  array
   */
  function sql_dbUserChannelPrivs($uID, $sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $columns = array(
      "ts2_channel_privileges.i_cp_id AS i_cp_id",
      "ts2_channel_privileges.i_cp_client_id AS i_cp_client_id",
      "ts2_channel_privileges.i_cp_channel_id AS i_cp_channel_id",
      "ts2_channel_privileges.b_cp_flag_admin AS b_cp_flag_admin",
      "ts2_channel_privileges.b_cp_flag_autoop AS b_cp_flag_autoop",
      "ts2_channel_privileges.b_cp_flag_autovoice AS b_cp_flag_autovoice",
      "ts2_channels.s_channel_name AS s_cp_channel_name",
      "ts2_channels.i_channel_parent_id AS i_cp_channel_parent_id",
      "ts2_channels.b_channel_flag_default AS b_cp_channel_flag_default",
      "ts2_channels.b_channel_flag_hierarchical AS b_cp_channel_flag_hierarchical",
      "ts2_channels.b_channel_flag_moderated AS b_cp_channel_flag_moderated",
    );
    
    $sRes = $this->_fetchsqltable($this->_sqlcall("SELECT " . implode(", ", $columns) . " FROM ts2_channel_privileges LEFT JOIN ts2_channels ON i_cp_channel_id = i_channel_id WHERE i_cp_server_id = " . intval($sID) . " AND i_cp_client_id = " . intval($uID)));
    
    if(!is_array($sRes)) {
      return FALSE;
    }
    
    return $sRes;
  }
  
  /**
   * Returns channel privs of a registered user on a virtual server from the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $uID
   * @param   integer $cID
   * @param   array   $pData
   * @param   integer $sID
   * @return  array
   */
  function sql_dbUpdateUserChannelPrivs($uID, $cID, $pData = array(), $sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $sQuery = array();
    
    if(array_key_exists("b_cp_flag_admin", $pData)) $sQuery[] = "b_cp_flag_admin = " . ($pData["b_cp_flag_admin"] ? -1 : 0);
    if(array_key_exists("b_cp_flag_autoop", $pData)) $sQuery[] = "b_cp_flag_autoop = " . ($pData["b_cp_flag_autoop"] ? -1 : 0);
    if(array_key_exists("b_cp_flag_autovoice", $pData)) $sQuery[] = "b_cp_flag_autovoice = " . ($pData["b_cp_flag_autovoice"] ? -1 : 0);
    
    if(!count($this->_fetchsqltable($this->_sqlcall("SELECT * FROM ts2_channel_privileges WHERE i_cp_server_id = " . intval($sID) . " AND i_cp_channel_id = " . intval($cID) . " AND i_cp_client_id = " . intval($uID))))) {
      $this->_sqlcall("INSERT INTO ts2_channel_privileges (i_cp_server_id, i_cp_channel_id, i_cp_client_id, b_cp_flag_admin, b_cp_flag_autoop, b_cp_flag_autovoice) VALUES(" . intval($sID) . ", " . intval($cID) . ", " . intval($uID) . ", 0, 0, 0)");
    }
    
    if($sRes = ($this->_sqlcall("UPDATE ts2_channel_privileges SET " . implode($sQuery, ", ") . " WHERE i_cp_server_id = " . intval($sID) . " AND i_cp_channel_id = " . intval($cID) . " AND i_cp_client_id = " . intval($uID)) == CYTS_OK)) {
      $this->sql_rehash();
    }
    
    return $sRes;
  }
  
  /**
   * Deletes useless channel privs of a registered user on a virtual server from the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $uID
   * @param   integer $sID
   * @return  boolean
   */
  function sql_dbCleanupUserChannelPrivs($uID, $sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    return ($this->_sqlcall("DELETE FROM ts2_channel_privileges WHERE b_cp_flag_admin = 0 AND b_cp_flag_autoop = 0 AND b_cp_flag_autovoice = 0 AND i_cp_server_id = " . intval($sID) . " AND i_cp_client_id = " . intval($uID)) == CYTS_OK);
  }
  
  /**
   * Deletes orphan channel privs on a virtual server from the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $sID
   * @return  boolean
   */
  function sql_CleanupChannelPrivs($sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $columns = array(
      "ts2_channel_privileges.i_cp_id AS i_cp_id",
      "ts2_channel_privileges.i_cp_client_id AS i_cp_client_id",
      "ts2_channel_privileges.i_cp_channel_id AS i_cp_channel_id",
      "ts2_channels.s_channel_name AS s_cp_channel_name",
    );
    
    $privs = $this->_fetchsqltable($this->_sqlcall("SELECT " . implode(", ", $columns) . " FROM ts2_channel_privileges LEFT JOIN ts2_channels ON i_cp_channel_id = i_channel_id WHERE i_cp_server_id = " . intval($sID)));
    
    foreach($privs as $priv) {
      if(empty($priv["s_cp_channel_name"])) $this->sql_dbDeleteUserChannelPriv($priv["i_cp_id"], $sID);
    }
    
    return TRUE;
  }
  
  /**
   * Deletes a specified channel priv of a registered user on a virtual server from the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $pID
   * @param   integer $sID
   * @return  boolean
   */
  function sql_dbDeleteUserChannelPriv($pID, $sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    return ($this->_sqlcall("DELETE FROM ts2_channel_privileges WHERE i_cp_server_id = " . intval($sID) . " AND i_cp_id = " . intval($pID)) == CYTS_OK);
  }
  
  /**
   * Returns a list of SA accounts on a virtual server from the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $limit
   * @param   integer $offset
   * @param   integer $sID
   * @return  array
   */
  function sql_dbAdminUserList($limit = null, $offset = null, $sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $limit = ($limit) ? " LIMIT " . $limit : "";
    
    if($limit) {
      $offset = ($offset) ? " OFFSET " . $offset : "";
    } else {
      $offset = "";
    }
    
    $sRes = $this->_fetchsqltable($this->_sqlcall("SELECT * FROM ts2_clients WHERE b_client_privilege_serveradmin = -1 AND i_client_server_id = " . intval($sID) . $limit . $offset));
    
    for($i = 0; $i < count($sRes); $i++) {
      $sRes[$i][1] = $this->info_translateDbDate($sRes[$i]["dt_client_created"]);
      $sRes[$i][2] = $this->info_translateDbDate($sRes[$i]["dt_client_lastonline"]);
      $sRes[$i]["dt_client_created"] = $sRes[$i][1];
      $sRes[$i]["dt_client_lastonline"] = $sRes[$i][2];
    }
    
    return $sRes;
  }
  
  /**
   * Returns information about a SSA from the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $uID
   * @return  array
   */
  function sql_dbAdminUserInfo($uID, $sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $sRes = $this->_fetchsqllist($this->_sqlcall("SELECT * FROM ts2_clients WHERE b_client_privilege_serveradmin = -1 AND i_client_server_id = " . intval($sID) . " AND i_client_id = " . intval($uID)));
    
    if(!is_array($sRes)) {
      return FALSE;
    }
    
    $sRes["dt_client_created"] = $this->info_translateDbDate($sRes["dt_client_created"]);
    $sRes["dt_client_lastonline"] = $this->info_translateDbDate($sRes["dt_client_lastonline"]);
    
    return $sRes;
  }
  
  /**
   * Returns the number of SA accounts on a virtual server.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $sID
   * @return  integer
   */
  function sql_dbAdminUserCount($sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $sRes = $this->_fetchsqllist($this->_sqlcall("SELECT COUNT(*) as i_client_count FROM ts2_clients WHERE b_client_privilege_serveradmin = -1 AND i_client_server_id = " . intval($sID)));
    
    if(!is_array($sRes)) {
      return FALSE;
    }
    
    return intval($sRes["i_client_count"]);
  }
  
  /**
   * Returns a list of SSA accounts from the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $limit
   * @param   integer $offset
   * @return  array
   */
  function sql_dbSUserList($limit = null, $offset = null)
  {
    $sID = 0;
    
    $limit = ($limit) ? " LIMIT " . $limit : "";
    
    if($limit) {
      $offset = ($offset) ? " OFFSET " . $offset : "";
    } else {
      $offset = "";
    }
    
    $sRes = $this->_fetchsqltable($this->_sqlcall("SELECT * FROM ts2_clients WHERE b_client_privilege_serveradmin = -1 AND i_client_server_id = " . intval($sID) . $limit . $offset));
    
    for($i = 0; $i < count($sRes); $i++) {
      $sRes[$i][1] = $this->info_translateDbDate($sRes[$i]["dt_client_created"]);
      $sRes[$i][2] = $this->info_translateDbDate($sRes[$i]["dt_client_lastonline"]);
      $sRes[$i]["dt_client_created"] = $sRes[$i][1];
      $sRes[$i]["dt_client_lastonline"] = $sRes[$i][2];
    }
    
    return $sRes;
  }
  
  /**
   * Returns information about a SSA from the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $uID
   * @return  array
   */
  function sql_dbSUserInfo($uID)
  {    
    $sRes = $this->_fetchsqllist($this->_sqlcall("SELECT * FROM ts2_clients WHERE i_client_server_id = 0 AND i_client_id = " . intval($uID)));
    
    if(!is_array($sRes)) {
      return FALSE;
    }
    
    $sRes["dt_client_created"] = $this->info_translateDbDate($sRes["dt_client_created"]);
    $sRes["dt_client_lastonline"] = $this->info_translateDbDate($sRes["dt_client_lastonline"]);
    
    return $sRes;
  }
  
  /**
   * Returns the number of SSA accounts from the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @return  integer
   */
  function sql_dbSUserCount()
  {
    $sID = 0;
    
    $sRes = $this->_fetchsqllist($this->_sqlcall("SELECT COUNT(*) as i_client_count FROM ts2_clients WHERE b_client_privilege_serveradmin = -1 AND i_client_server_id = " . intval($sID)));
    
    if(!is_array($sRes)) {
      return FALSE;
    }
    
    return intval($sRes["i_client_count"]);
  }
  
  /**
   * Checks if an account exists and has admin permissions.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   string  $uName
   * @param   string  $uPass
   * @param   integer $sID
   * @param   boolean $isMd5
   * @return  boolean
   */
  function sql_dbVerifyAdminAccount($uName, $uPass, $sID, $isMd5 = FALSE)
  {
    $uName = addcslashes($uName, "\"");
    $uPass = ($isMd5) ? md5($uPass) : addcslashes($uPass, "\"");    
    
    $sRes = $this->_fetchsqltable($this->_sqlcall("SELECT * FROM ts2_clients WHERE b_client_privilege_serveradmin = -1 AND s_client_name = \"" . $uName . "\" AND s_client_password = \"" . $uPass . "\" AND i_client_server_id = " . intval($sID)));
    
    return (count($sRes)) ? TRUE : FALSE;
  }
  
  /**
   * Checks if a loginname is in use.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   string  $uName
   * @param   integer $sID
   * @return  boolean
   */
  function sql_dbVerifyLoginname($uName, $sID = 0)
  {        
    $sRes = $this->_fetchsqltable($this->_sqlcall("SELECT * FROM ts2_clients WHERE s_client_name = \"" . addcslashes($uName, "\"") . "\" AND i_client_server_id = " . intval($sID)));
    
    return (count($sRes)) ? TRUE : FALSE;
  }
  
  /**
   * Checks if a channelname is in use.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   string  $cName
   * @param   integer $sID
   * @return  boolean
   */
  function sql_dbVerifyChannelname($cName, $sID = 0)
  {        
    $sRes = $this->_fetchsqltable($this->_sqlcall("SELECT * FROM ts2_channels WHERE s_channel_name = \"" . addcslashes($cName, "\"") . "\" AND i_channel_server_id = " . intval($sID)));
    
    return (count($sRes)) ? TRUE : FALSE;
  }
  
  /**
   * Changes admin permissions for an account in the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $uID
   * @param   boolean $uSA
   * @param   integer $sID
   * @return  boolean
   */
  function sql_dbUserChangeSA($uID, $uSA = FALSE, $sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $uSA = ($uSA) ? -1 : 0;

    return ($this->_sqlcall("UPDATE ts2_clients SET b_client_privilege_serveradmin = " . $uSA . " WHERE i_client_id = " . intval($uID) . " AND i_client_server_id = " . intval($sID)) == CYTS_OK);
  }
  
  /**
   * Creates a new account in the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   string  $lName
   * @param   string  $lPass
   * @param   boolean $lSA
   * @param   integer $sID
   * @param   boolean $isMd5
   * @return  boolean
   */
  function sql_dbUserAdd($lName, $lPass, $lSA = FALSE, $sID = null, $isMd5 = FALSE)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    if($this->sql_dbVerifyLoginname($lName, $sID)) {
      $this->_debug(null, "Loginname already in use", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $lName = addcslashes($lName, "\"");
    $lPass = ($isMd5) ? md5($lPass) : addcslashes($lPass, "\"");
    $lSA = ($lSA) ? -1 : 0;
    $lDate = date("dmYhis000");

    return ($this->_sqlcall("INSERT INTO ts2_clients (i_client_server_id, b_client_privilege_serveradmin, s_client_name, s_client_password, dt_client_created) VALUES(" . intval($sID) . ", " . intval($lSA) . ", \"". $lName ."\", \"". $lPass ."\", " . $lDate . ")") == CYTS_OK);
  }
  
  /**
   * Modifies an account in the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $uID
   * @param   array   $pData
   * @param   integer $sID
   * @param   boolean $isMd5
   * @return  boolean
   */
  function sql_dbUserEdit($uID, $pData = array(), $sID = null, $isMd5 = FALSE)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $sQuery = array();
    
    if(array_key_exists("b_client_privilege_serveradmin", $pData)) $sQuery[] = "b_client_privilege_serveradmin = " . ($pData["b_client_privilege_serveradmin"] ? -1 : 0);
    if(array_key_exists("s_client_password", $pData)) $sQuery[] = "s_client_password = \"" . (($isMd5) ? md5($pData["s_client_password"]) : addcslashes($pData["s_client_password"], "\"")) . "\"";
    if(array_key_exists("s_client_name", $pData)) {
      if($this->sql_dbVerifyLoginname($pData["s_client_name"], $sID)) {
        $this->_debug(null, "Loginname already in use", CYTS_DEBUG_ERROR);
        return FALSE;
      }
      $sQuery[] = "s_client_name = \"" . addcslashes($pData["s_client_name"], "\"") . "\"";
    }
    
    if(!count($sQuery)) return TRUE;
    
    if($sRes = ($this->_sqlcall("UPDATE ts2_clients SET " . implode($sQuery, ", ") . " WHERE i_client_id = " . intval($uID) . " AND i_client_server_id = " . intval($sID)) == CYTS_OK)) {
      $this->sql_rehash();
    }
    
    return $sRes;
  }
  
  /**
   * Deletes an account from the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $uID
   * @param   integer $sID
   * @return  boolean
   */
  function sql_dbUserDel($uID, $sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    return ($this->_sqlcall("DELETE FROM ts2_clients WHERE i_client_id = " . intval($uID) . " AND i_client_server_id = " . intval($sID)) == CYTS_OK);
  }
  
  /**
   * Creates a new SSA account in the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   string  $lName
   * @param   string  $lPass
   * @param   boolean $isMd5
   * @return  boolean
   */
  function sql_dbSUserAdd($lName, $lPass, $isMd5 = FALSE)
  {    
    if($this->sql_dbVerifyLoginname($lName, 0)) {
      $this->_debug(null, "Loginname already in use", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $lName = addcslashes($lName, "\"");
    $lPass = ($isMd5) ? md5($lPass) : addcslashes($lPass, "\"");
    $lSA = -1;
    $lDate = date("dmYhis000");

    return ($this->_sqlcall("INSERT INTO ts2_clients (i_client_server_id, b_client_privilege_serveradmin, s_client_name, s_client_password, dt_client_created) VALUES(0, " . intval($lSA) . ", \"". $lName ."\", \"". $lPass ."\", " . $lDate . ")") == CYTS_OK);
  }
  
  /**
   * Modifies a SSA account in the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $uID
   * @param   array   $pData
   * @param   boolean $isMd5
   * @return  boolean
   */
  function sql_dbSUserEdit($uID, $pData = array(), $isMd5 = FALSE)
  {
    $sQuery = array();
    
    if(array_key_exists("s_client_password", $pData)) $sQuery[] = "s_client_password = \"" . (($isMd5) ? md5($pData["s_client_password"]) : addcslashes($pData["s_client_password"], "\"")) . "\"";
    if(array_key_exists("s_client_name", $pData)) {
      if($this->sql_dbVerifyLoginname($pData["s_client_name"], 0)) {
        $this->_debug(null, "Loginname already in use", CYTS_DEBUG_ERROR);
        return FALSE;
      }
      $sQuery[] = "s_client_name = \"" . addcslashes($pData["s_client_name"], "\"") . "\"";
    }
    
    if(!count($sQuery)) return TRUE;
    
    if($sRes = ($this->_sqlcall("UPDATE ts2_clients SET " . implode($sQuery, ", ") . " WHERE i_client_id = " . intval($uID) . " AND i_client_server_id = 0") == CYTS_OK)) {
      $this->sql_rehash();
    }
    
    return $sRes;
  }
  
  /**
   * Deletes a SSA account from the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $uID
   * @return  boolean
   */
  function sql_dbSUserDel($uID)
  {    
    return ($this->_sqlcall("DELETE FROM ts2_clients WHERE i_client_id = " . intval($uID) . " AND i_client_server_id = 0") == CYTS_OK);
  }
  
  /**
   * Returns a list of IP bans on a virtual server from the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $sID
   * @return  array
   */
  function sql_dbBanList($sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $sRes = $this->_fetchsqltable($this->_sqlcall("SELECT * FROM ts2_bans WHERE i_ban_server_id = " . intval($sID)));
    
    for($i = 0; $i < count($sRes); $i++) {
      $sRes[$i][0] = $this->info_translateDbDate($sRes[$i]["dt_ban_created"]);
      $sRes[$i]["dt_ban_created"] = $sRes[$i][0];
    }
    
    return $sRes;
  }
  
  /**
   * Returns information about an IP ban from the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   integer $bID
   * @param   integer $uID
   * @return  array
   */
  function sql_dbBanInfo($bID, $sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $sRes = $this->_fetchsqllist($this->_sqlcall("SELECT * FROM ts2_bans WHERE i_ban_server_id = " . intval($sID) . " AND i_ban_id = " . intval($bID)));
    
    if(!is_array($sRes)) {
      return FALSE;
    }
    
    $sRes["dt_ban_created"] = $this->info_translateDbDate($sRes["dt_ban_created"]);
    
    return $sRes;
  }
  
  /**
   * Returns the number of IP bans on a virtual server from the database.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @return  integer
   */
  function sql_dbBanCount($sID = null)
  {
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $sRes = $this->_fetchsqllist($this->_sqlcall("SELECT COUNT(*) as i_ban_count FROM ts2_bans WHERE i_ban_server_id = " . intval($sID)));
    
    if(!is_array($sRes)) {
      return FALSE;
    }
    
    return intval($sRes["i_ban_count"]);
  }
  
  /**
   * Reloads the server and permission settings of a virtual server.
   * 
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @return  boolean
   */
  function sql_rehash()
  {
    return ($this->_extendcall("rehash") == CYTS_OK);
  }
  
  /**
   * Returns the version of the SQLite library or MySQL server used by TeamSpeak.
   * 
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   boolean $is_sqlite
   * @return  string
   */
  function sql_version($is_sqlite = TRUE)
  {
    $func = ($is_sqlite) ? "sqlite_version()" : "version()";
    $sRes = $this->_fetchsqllist($this->_sqlcall("SELECT " . $func . " as s_sql_version"));
    
    if(!is_array($sRes)) {
      return FALSE;
    }
    
    return $sRes["s_sql_version"];
  }
  
  /**
   * Returns the ID of the last row insert from the connection to the database.
   * 
   * @author  Sven Paulsen
   * @version 2.5
   * @access  public
   * @param   boolean $is_sqlite
   * @return  string
   */
  function sql_lastInsertId($is_sqlite = TRUE)
  {
    $func = ($is_sqlite) ? "last_insert_rowid()" : "last_insert_id()";
    $sRes = $this->_fetchsqllist($this->_sqlcall("SELECT " . $func . " as i_sql_insert_id"));
    
    if(!is_array($sRes)) {
      return FALSE;
    }
    
    return $sRes["i_sql_insert_id"];
  }

  /**
   * Authenticates with the servers web interface.
   * 
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $wiPort
   * @param   integer $sTimeout
   * @return  boolean
   */
  function wi_login($wiPort = 14534, $sTimeout = 3)
  {
    if($this->isSAdmin) {
      $this->wiCookie = null;
      $this->wiPort = $wiPort;
      $sRet = $this->_wipost("/login.tscmd", array("username" => $this->user, "password" => $this->pass, "superadmin" => 1), $sTimeout);
      if(!$sRet || !isset($sRet[0]["Location"]) || $sRet[0]["Location"] != "index.html" || !isset($sRet[0]["Set-Cookie"])) {
        $this->wiPort = FALSE;
        return FALSE;
      } else {
        $this->wiCookie = strtr($sRet[0]["Set-Cookie"], "; path=/", "");
        if($this->udp) $this->wi_select();
        return TRUE;
      }
    } elseif($this->isAdmin) {
      $this->wiCookie = null;
      $this->wiPort = $wiPort;
      $sRet = $this->_wipost("/login.tscmd", array("username" => $this->user, "password" => $this->pass, "serverport" => $this->udp), $sTimeout);
      if(!$sRet || !isset($sRet[0]["Location"]) || $sRet[0]["Location"] != "index.html" || !isset($sRet[0]["Set-Cookie"])) {
        $this->wiPort = FALSE;
        return FALSE;
      } else {
        $this->wiCookie = strtr($sRet[0]["Set-Cookie"], "; path=/", "");
        return TRUE;
      }
    } else {
      return FALSE;
    }
  }

  /**
   * Selects a virtual server through the servers web interface.
   * 
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $sID
   * @return  boolean
   */
  function wi_select($sID = null)
  {
    if(!$this->_wiconnected()) {
      return FALSE;
    }
    
    $sID = ($sID) ? $sID : $this->sid;
    
    if(!$sID) {
      $this->_debug(null, "Missing virtual server ID", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $this->_wiget("/select_server.tscmd?serverid=" . $sID);
  }

  /**
   * Returns the servers settings in an array.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @return  array
   */
  function wi_readServerSettings()
  {
    if(!$sRead = $this->_wiget("/server_manager_settings.html")) {
      return FALSE;
    }

    if(!preg_match_all('/<input.*?name="([a-zA-Z0-9]+)".*?value="(.*?)".*?>/im', $sRead[1], $matches)) {
      $this->_debug(null, "Invalid html template file format", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $data = array();
    
    foreach($matches[1] as $key => $val) {
      if(strstr($matches[0][$key], 'type="checkbox"')) {
        $data[$val] = (strstr($matches[0][$key], "checked")) ? 1 : 0;
      } else {
        $data[$val] = $matches[2][$key];
      }
    }
    
    if(preg_match('/<input.*?name="serverudpport".*?value="(.*?)">*/im', $sRead[1], $matches)) {
      $data["serverudpport"] = $matches[1];
    } else {
      $this->_debug(null, "Invalid html template file format", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    if(preg_match('/<option.*?selected.*?value="(\d+)".*?>*/im', $sRead[1], $matches)) {
      $data["servertype"] = $matches[1];
    } else {
      $this->_debug(null, "Invalid html template file format", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    return $data;
  }

  /**
   * Modifies a servers settings through the web interface.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $sData
   * @return  boolean
   */
  function wi_writeServerSettings($sData = array())
  {
    if(!$cData = $this->wi_readServerSettings()) {
      return FALSE;
    }
    
    $sData = array_merge($cData, $sData);
    $sKeys = array("servername", "serverwelcomemessage", "serverpassword", "servertype", "serverwebpostposturl", "serverwebpostlinkurl");
    $ssKeys = array("servermaxusers", "CODECCelp51", "CODECSPEEX2150", "CODECCelp63", "CODECSPEEX3950", "CODECGSM148", "CODECSPEEX5950", "serverudpport", "CODECGSM164", "CODECSPEEX8000", "CODECWindowsCELP52", "CODECSPEEX11000", "CODECSPEEX15000", "CODECSPEEX18200", "CODECSPEEX24600");
    
    foreach($sData as $key => $val) {
      if((!in_array($key, $sKeys) && !in_array($key, $ssKeys) && $this->isSAdmin) || (!in_array($key, $sKeys) && !$this->isSAdmin) || !$this->isAdmin) {
        unset($sData[$key]);
      }
    }
    
    if(!$this->_wipost("/settings_server.tscmd", $sData)) {
      return FALSE;
    }
    
    if(!$cData = $this->wi_readServerSettings()) {
      return FALSE;
    }
    
    foreach($sData as $key => $val) {
      if($cData[$key] != $val) return FALSE;
    }
      
    return TRUE; 
  }

  /**
   * Returns the servers global settings in an array.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @return  array
   */
  function wi_readHostSettings()
  {
    if(!$sRead = $this->_wiget("/server_basic_settings.html")) {
      return FALSE;
    }

    if(!preg_match_all('/<input.*?name="([a-zA-Z0-9_]+)".*?value="(.*?)".*?>/im', $sRead[1], $matches)) {
      $this->_debug(null, "Invalid html template file format", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $data = array();
    
    foreach($matches[1] as $key => $val) {
      if(strstr($matches[0][$key], 'type="checkbox"')) {
        $data[$val] = (strstr($matches[0][$key], "checked")) ? 1 : 0;
      } else {
        $data[$val] = $matches[2][$key];
      }
    }
    
    if(preg_match('/<option value="(.*?)" selected>/im', $sRead[1], $matches)) {
      $data["basic_country"] = $matches[1];
    }
      
    return $data;
  }

  /**
   * Modifies the servers global settings through the web interface.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   integer $sData
   * @return  boolean
   */
  function wi_writeHostSettings($sData = array())
  {
    if(!$cData = $this->wi_readHostSettings()) {
      return FALSE;
    }
    
    $sData = array_merge($cData, $sData);
    $ssKeys = array("basic_ispname", "basic_adminemail", "basic_isplinkurl", "basic_country", "basic_listpublic", "webpost_posturl", "webpost_enabled", "spam_maxcommands", "spam_inseconds");
    
    foreach($sData as $key => $val) {
      if(!in_array($key, $ssKeys)) {
        unset($sData[$key]);
      }
    }
    
    if(!$this->_wipost("/settings_basic.tscmd", $sData)) {
      return FALSE;
    }
    
    if(!$cData = $this->wi_readHostSettings()) {
      return FALSE;
    }
    
    foreach($sData as $key => $val) {
      if($cData[$key] != $val) return FALSE;
    }
      
    return TRUE;
  }

  /**
   * Returns a servers group permission settings in an array.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   string $class
   * @return  array
   */
  function wi_readGroupPermissions($class)
  {
    $class = strtolower($class);
    $classes = array("sa", "ca", "op", "v", "r", "u");
    
    if(!in_array($class, $classes, TRUE)) {
      $this->_debug(null, "Invalid usergroup", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    if(!$sRead = $this->_wiget("/server_manager_permission_" . $class . ".html")) {
      return FALSE;
    }
        
    if(!preg_match_all('/<input.*?name="([a-zA-Z_]+)".*?value="1"(checked)*>/im', $sRead[1], $matches)) {
      $this->_debug(null, "Invalid html template file format", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    $data = array();
    
    foreach($matches[1] as $key => $val) {
      if($matches[2][$key] == "checked") {
        $matches[2][$key] = 1;
      } else {
        $matches[2][$key] = 0;
      }
      $data[$val] = $matches[2][$key];
    }
    
    return $data;
  }

  /**
   * Modifies a servers group permission settings through the web interface.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   string  $class
   * @param   array   $sData
   * @return  boolean
   */
  function wi_writeGroupPermissions($class, $sData = array())
  {
    $class = strtolower($class);
    $classes = array("sa", "ca", "op", "v", "r", "u");
    
    if(!in_array($class, $classes, TRUE)) {
      return FALSE;
    }
    
    if(!$cData = $this->wi_readGroupPermissions($class)) {
      $this->_debug(null, "Invalid usergroup", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    foreach($cData as $key => $val) {
      if(isset($sData[$key]) && ($sData[$key] == 1 || $sData[$key] == 0)) {
        $cData[$key] = $sData[$key];
      }
    }
      
    if(!$this->_wipost("/permissions_server.tscmd", $cData)) {
      return FALSE;
    }
    
    if(!$nData = $this->wi_readGroupPermissions($class)) {
      return FALSE;
    }
    
    foreach($cData as $key => $val) {
      if($nData[$key] != $val) return FALSE;
    }
    
    return TRUE; 
  }

  /**
   * Sends a request to the web interface using POST.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   string  $sFile
   * @param   array   $sCall
   * @param   integer $sTimeout
   * @return  string
   */
  function _wipost($sFile, $sCall = array(), $sTimeout = 3)
  {
    if(!$this->_wiconnected()) {
      return FALSE;
    }

    $pCall = array();

    foreach($sCall as $key => $val) {
      $pCall[] = $key . "=" .urlencode($val);
    }

    $sCall = implode("&", $pCall);

    $fp = @fsockopen($this->server, $this->wiPort, $errNo, $errStr, $sTimeout);

    if(!$fp) {
      $this->_debug(null, "HTTP connection error " . $errNo . " (" . $errStr . ")", CYTS_DEBUG_ERROR);
      return FALSE;
    }

    @fputs($fp, "POST " . $sFile . " HTTP/1.1\r\n");
    @fputs($fp, "Host: " . $this->server . "\r\n");
    @fputs($fp, "Content-Type: application/x-www-form-urlencoded\r\n");
    @fputs($fp, "User-Agent: CyTS/" . CYTS_VERSION . " (" . php_uname("s"). "; PHP " . phpversion() . "; " . php_sapi_name() . "; " . php_uname("m") . ")\r\n");
    if($this->wiCookie) @fputs($fp, "Cookie: " . $this->wiCookie . "\r\n");
    @fputs($fp, "Content-Length: ". strlen($sCall) ."\r\n");
    @fputs($fp, "Connection: close\r\n\r\n");
    @fputs($fp, $sCall);

    $header = array();

    do {
      $cRead = @fgets($fp);
      if(preg_match('/([A-Za-z0-9_-]+): *(.*)\r\n/i', $cRead, $content)) {
        $header[$content[1]] = $content[2];
      } elseif(trim($cRead) != "") {
        $header[] = trim($cRead);
      }
    } while($cRead != "\r\n");
    
    if(!isset($header["Server"]) || strstr($header["Server"], "Indy") === FALSE) {
      $this->_debug(null, "Host is not a TeamSpeak server", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    if(!isset($header[0]) || !preg_match('/HTTP\/([\.\d]+) (\d+) ([\w\s]+)/i', $header[0], $code)) {
      $this->_debug(null, "Invalid HTTP header", CYTS_DEBUG_ERROR);
      return FALSE;
    } else {
      if($code[2] != 200 && $code[2] != 302) {
        $this->_debug(null, "HTTP error " . $code[2] . " (" . $code[3] . ")", CYTS_DEBUG_ERROR);
        return FALSE;
      }
    }

    if(!isset($header["Content-Length"])) {
      $content = "";
    } else {
      $dLen =  $header["Content-Length"];
      $content = "";
      while($dLen >= 1024) {
        $content .= fread($fp, 1024);
        $dLen -= 1024;
      }
      if($dLen > 0) $content .= fread($fp, $dLen);
    }

    @fclose($fp);
    
    $sRes = array($header, $content);
        
    $this->_debug($sFile, $sRes, CYTS_DEBUG_INFO);

    return $sRes;
  }

  /**
   * Sends a request to the web interface using GET.
   *
   * @author  Steven Barth
   * @version 2.5
   * @access  public
   * @param   string  $sFile
   * @param   integer $sTimeout
   * @return  string
   */
  function _wiget($sFile, $sTimeout = 3)
  {
    if(!$this->_wiconnected()) {
      return FALSE;
    }

    $fp = @fsockopen($this->server, $this->wiPort, $errNo, $errStr, $sTimeout);

    if(!$fp) {
      $this->_debug(null, "HTTP connection error " . $errNo . " (" . $errStr . ")", CYTS_DEBUG_ERROR);
      return FALSE;
    }

    @fputs($fp, "GET " . $sFile . " HTTP/1.1\r\n");
    @fputs($fp, "Host: " . $this->server . "\r\n");
    @fputs($fp, "User-Agent: CyTS/" . CYTS_VERSION . " (" . php_uname("s"). "; PHP " . phpversion() . "; " . php_sapi_name() . "; " . php_uname("m") . ")\r\n");
    if($this->wiCookie) @fputs($fp, "Cookie: ". $this->wiCookie . "\r\n");
    @fputs($fp, "Connection: close\r\n\r\n");

    $header = array();

    do {
      $cRead = @fgets($fp);
      if(preg_match('/([A-Za-z0-9_-]+): *(.*)\r\n/i', $cRead, $content)) {
        $header[$content[1]] = $content[2];
      } elseif(trim($cRead) != "") {
        $header[] = trim($cRead);
      }
    } while($cRead != "\r\n");

    if(!isset($header["Server"]) || strstr($header["Server"], "Indy") === FALSE) {
      $this->_debug(null, "Host is not a TeamSpeak server", CYTS_DEBUG_ERROR);
      return FALSE;
    }
    
    if(!isset($header[0]) || !preg_match('/HTTP\/([\.\d]+) (\d+) ([\w\s]+)/i', $header[0], $code)) {
      $this->_debug(null, "Invalid HTTP header", CYTS_DEBUG_ERROR);
      return FALSE;
    } else {
      if($code[2] != 200 && $code[2] != 302) {
        $this->_debug(null, "HTTP error " . $code[2] . " (" . $code[3] . ")", CYTS_DEBUG_ERROR);
        return FALSE;
      }
    }
    
    if(!isset($header["Content-Length"])) {
      $content = "";
    } else {
      $dLen =  $header["Content-Length"];
      $content = "";
      while($dLen >= 1024) {
        $content .= fread($fp, 1024);
        $dLen -= 1024;
      }
      if($dLen > 0) $content .= fread($fp, $dLen);
    }

    @fclose($fp);

    $sRes = array($header, $content);
    
    $this->_debug($sFile, $sRes, CYTS_DEBUG_INFO);

    return $sRes;
  }

  /**
   * Executes one or more commands on the server.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  private
   * @param   string|array $sCall
   * @return  string|array
   */
  function _fastcall($sCall)
  {
    $sRead = array();

    if(is_array($sCall)) {
      foreach($sCall as $sCmd) {
        $sRead[] = $this->_fastcall($sCmd);
      }
    } else {
      $this->_writecall($sCall);
      $sRead = $this->_readcall();
      $this->_debug($sCall, $sRead, CYTS_DEBUG_INFO);
    }

    return $sRead;
  }
  
  /**
   * Executes one SQL command on the server.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  private
   * @param   string $sCall
   * @return  string|array
   */
  function _sqlcall($sCall)
  {    
    $wspace = strpos($sCall, " ");
    $sqlcmd = substr($sCall, 0, $wspace);
    
    if($sqlcmd != "SELECT") {
      $sRead = $this->_extendcall("sql " . $sCall);
    } else {
      $sRead = $this->_fastcall("sql " . $sCall);
    }

    if(!$sRead || (!is_array($sRead) && $sRead != CYTS_OK)) {
      return FALSE;
    }
    
    if(!is_array($sRead)) {
       return $sRead;
    }
        
    if($sRead[count($sRead)-1] == CYTS_INVALID_ERROR) {
      $this->_debug(null, $sRead[0], CYTS_DEBUG_ERROR);
      return CYTS_INVALID_ERROR;
    }
    
    $this->_fastcall("checkserverok");
    
    return $sRead;
  }

  /**
   * Executes one or more commands on the server and resets the channel and playerlists.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  private
   * @param   string|array $sCall
   * @return  string|array
   */
  function _extendcall($sCall)
  {
    $sRead = $this->_fastcall($sCall);
    
    if(!is_array($sRead) && $sRead != CYTS_OK) {
      return $sRead;
    }
    
    $this->cList = null;
    $this->uList = null;
    $this->sList = null;
    $this->sInfo = null;
    $this->dbuList = null;
    $this->dbsuList = null;
    $this->dbsList = null;
    $this->sDescription = null;
    
    $sCmd = (strstr($sCall, " ") !== FALSE) ? str_replace(strstr($sCall, " "), "", $sCall) : $sCall;
    
    $this->_debug(null, "List cache has been cleared during query (" . $sCmd . ")", CYTS_DEBUG_WARN);

    return $sRead;
  }

  /**
   * Sends data to the stream.
   * 
   * @author  Sven Paulsen
   * @version 2.5
   * @access  private
   * @param   string $sCall
   * @return  string
   */
  function _writecall($sCall)
  {
    if(!$this->_connected()) {
      return;
    }

    @fwrite($this->sCon, trim($sCall) . "\n");
  }

  /**
   * Reads a line of data from the stream.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  private
   * @return  string
   */
  function _readline()
  {
    if(!$this->_connected()) {
      return;
    }

    $sLine = @fgets($this->sCon);

    return trim($sLine);
  }

  /**
   * Reads the servers reply from the stream.
   * 
   * @author  Sven Paulsen
   * @version 2.5
   * @access  private
   * @return  string
   */
  function _readcall()
  {
    if(!$this->_connected()) {
      return;
    }

    $sLine = null;
    $sRead = array();

    do {
      $sLine = $this->_readline($this->sCon);      
      $sRead[] = $sLine;
    } while($sLine != CYTS_SYN && $sLine != CYTS_OK && strtolower(substr($sLine, 0, strlen(CYTS_ERROR))) != CYTS_ERROR);

    return (count($sRead) == 1) ? $sLine : $sRead;
  }

  /**
   * Fetches a specified server reply in table format as an array.
   * 
   * @author  Sven Paulsen
   * @version 2.5
   * @access  private
   * @param   array $sReply
   * @return  array
   */
  function _fetchtable($sReply)
  {
    if(!is_array($sReply) || count($sReply) < 2) {
      return FALSE;
    }

    $sTable = array();

    $sHeader = explode("\t", array_shift($sReply));
    $sStatus = array_pop($sReply);

    array_unshift($sHeader, "unparsed");

    foreach($sReply as $sLine) {
      $sDat = explode("\t", trim($sLine));
      array_unshift($sDat, $sLine);
      foreach($sDat as $key => $val) {
        if(substr($val, 0, 1) == "\"" && substr($val, -1) == "\"") {
          $val = substr($val, 1, strlen($val) - 2);
        }
        $sRow[$key] = $val;
        $sRow[$sHeader[$key]] = $val;
      }
      $sTable[] = $sRow;
    }

    return $sTable;
  }

  /**
   * Fetches a specified server reply in list format as an array.
   * 
   * @author  Sven Paulsen
   * @version 2.5
   * @access  private
   * @param   array $sReply
   * @return  array
   */
  function _fetchlist($sReply)
  {
    if(!is_array($sReply) || count($sReply) < 1) {
      return FALSE;
    }

    $sList = array();

    $sStatus = array_pop($sReply);

    foreach($sReply as $sLine) {
      $data = explode("=", $sLine, 2);
      if(isset($data[1])) {
        $sList[$data[0]] = $data[1];
      } else {
        $sList[] = $data[0];
      }
    }

    return $sList;
  }
  
  /**
   * Fetches a specified server reply in SQL table format as an array.
   * 
   * @author  Sven Paulsen
   * @version 2.5
   * @access  private
   * @param   array $sReply
   * @return  array
   */
  function _fetchsqltable($sReply)
  {
    if(!is_array($sReply)) {
      if($sReply != CYTS_OK) {
        return FALSE;
      } else {
        return array();
      }
    }
    
    $sTable = array();
    
    $sStatus = array_pop($sReply);
    
    for($i = 0; $i < count($sReply); $i++) {
      $sData = explode("\t", $sReply[$i]);
      foreach($sData as $sKey => $sVal) {
        list($dKey, $dVal) = explode("=", $sVal);
        $dVal = substr($dVal, 1, strlen($dVal) - 2);
        $sTable[$i][$sKey] = $dVal;
        $sTable[$i][$dKey] = $dVal;
      }
    }
    
    return $sTable;
  }
  
  /**
   * Fetches a specified server reply in SQL list format as an array.
   * 
   * @author  Sven Paulsen
   * @version 2.5
   * @access  private
   * @param   array $sReply
   * @return  array
   */
  function _fetchsqllist($sReply)
  {
    if(!is_array($sReply) || count($sReply) != 2) {
      return FALSE;
    }
    
    $sList = array();
    
    $sStatus = array_pop($sReply);
    
    for($i = 0; $i < count($sReply); $i++) {
      $sData = explode("\t", $sReply[$i]);
      foreach($sData as $sKey => $sVal) {
        list($dKey, $dVal) = explode("=", $sVal);
        $dVal = substr($dVal, 1, strlen($dVal) - 2);
        $sList[$dKey] = $dVal;
      }
    }
    
    return $sList;
  }
  
  /**
   * Fetches a specified snapshot row, removes the primary key and returns an array of keys and values.
   * 
   * @author  Sven Paulsen
   * @version 2.5
   * @access  private
   * @param   array  $rData
   * @param   string $rPrimary
   * @return  array
   */
  function _fetchsqlsnapshotrow($rData, $rPrimary = null) 
  {
    foreach($rData as $key => $val) {
      if(is_numeric($key)) unset($rData[$key]);
    }
    if($rPrimary) unset($rData[$rPrimary]);
      
    $keys = array_keys($rData);
    $vals = array_values($rData);
    
    return array($keys, $vals);
  }
  
  /**
   * Fetches a specified log line array into a multi-dimensional data array.
   *
   * @param  array $logs
   * @return array
   */
  function _fetchlog($logs)
  {
    $data = array();
    
    foreach($logs as $log) {
      list($dt, $type, $lvl, $cat, $event) = explode(",", $log, 5);
      
      if(!preg_match("/^(\d\d)-(\d\d)-(\d\d)\s(\d\d):(\d\d):(\d\d)$/", $dt, $dt_parts)) continue;
      $dt = $dt_parts[3] . "-" . $dt_parts[2] . "-" . $dt_parts[1] . " " . $dt_parts[4] . ":" . $dt_parts[5] . ":" . $dt_parts[6];
      
      if(!preg_match("/^\tSID:\s(\d+)\s+([^\r\n]+)\s*$/", $event, $event_parts)) {
        $event_sid = 0;
        $event_txt = trim($event);
      } else {
        $event_sid = intval($event_parts[1]);
        $event_txt = trim($event_parts[2]);
      }
      
      $data[] = array(
        "unparsed" => $log,
        "datetime" => strtotime($dt),
        "type" => ucfirst(strtolower($type)),
        "level" => ucfirst(strtolower($lvl)),
        "category" => ucfirst($cat),
        "server" => $event_sid,
        "event" => $event_txt,
      );
    }
    
    return $data;
  }

  /**
   * Callback function used to sort the servers playerlist.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  private
   * @param   array   $a
   * @param   array   $b
   * @return  integer
   */
  function _sortplayers($a, $b)
  {
    if(($a["pprivs"] & 1) != ($b["pprivs"] & 1)) {
      return (($a["pprivs"] & 1) == 1) ? -1 : 1;
    } elseif($a["cprivs"] != $b["cprivs"]) {
      if($a["cprivs"] == 0) {
        return 1;
      } elseif($b["cprivs"] == 0) {
        return -1;
      } else {
        return ($a["cprivs"] < $b["cprivs"]) ? -1 : 1;
      }
    } else {
      return strcmp(strtolower($a["nick"]), strtolower($b["nick"]));
    }
  }

  /**
   * Callback function used to sort the servers channellist.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  private
   * @param   array   $a
   * @param   array   $b
   * @return  integer
   */
  function _sortchannels($a, $b)
  {
    if($a["order"] != $b["order"]) {
      return strnatcmp($a["order"], $b["order"]);
    } else {
      return strcmp(strtolower($a["name"]), strtolower($b["name"]));
    }
  }

  /**
   * Returns TRUE if we"re connected to the servers TCP Query interface.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  private
   * @return  boolean
   */
  function _connected()
  {
    return (isset($this->sCon) && is_resource($this->sCon)) ? TRUE : FALSE;
  }

  /**
   * Returns TRUE if we"re connected to the servers web admin interface.
   *
   * @author  Sven Paulsen
   * @version 2.5
   * @access  private
   * @return  boolean
   */
  function _wiconnected()
  {
    return ($this->wiPort) ? TRUE : FALSE;
  }

  /**
   * Adds an element to the debug info array.
   * 
   * @author  Sven Paulsen
   * @version 2.5
   * @access  private
   * @param   string $cmd
   * @param   string $rpl
   * @param   string $lvl
   * @return  void
   */
  function _debug($cmd, $rpl, $lvl)
  {    
    $trace = debug_backtrace();

    $now = microtime();
    $sys = isset($trace[1]) ? $trace[1]["function"] : "main";

    if(substr($cmd, 0, 5) == "login") $cmd = "login ******* *******";
    if(substr($cmd, 0, 6) == "slogin") $cmd = "slogin ******* *******";
    
    if($lvl == CYTS_DEBUG_INFO) {
      $lvl = "Info";
    } elseif($lvl == CYTS_DEBUG_WARN) {
      $lvl = "Warning";
    } elseif($lvl == CYTS_DEBUG_ERROR) {
      $lvl = "Error";
    } else {
      $lvl = "Unknown";
    }

    $this->debug[] = array(
      "now" => $now,
      "sys" => $sys,
      "cmd" => $cmd,
      "rpl" => $rpl,
      "lvl" => $lvl,
    );
  }
}
