<?php namespace Comodojo\Cookies;

use \Comodojo\Cookies\CookieInterface\CookieInterface;
use \Comodojo\Exception\CookieException;

/**
 * Plain cookie
 * 
 * @package     Comodojo Spare Parts
 * @author      Marco Giovinazzi <info@comodojo.org>
 * @license     MIT
 *
 * LICENSE:
 * 
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 */

class Cookie implements CookieInterface {

    /*
     * The cookie name
     *
     * @var string
     */
    protected $name = null;

    /*
     * Cookie value (native string or serialized one)
     *
     * @var string
     */
    protected $value = null;

    /*
     *
     * @var
     */
    protected $expire = null;

    /*
     *
     * @var
     */
    protected $path = null;

    /*
     *
     * @var
     */
    protected $domain = null;

    /*
     *
     * @var
     */
    protected $secure = false;

    /*
     *
     * @var
     */
    protected $httponly = false;

    /**
     * Cookie constructor
     *
     * Setup cookie name
     *
     * @param   string   $name
     *
     * @throws \Comodojo\Exception\CookieException
     */
    public function __construct($name) {

        try {

            $this->setName($name);
            
        } catch (CookieException $ce) {
            
            throw $ce;

        }

    }

    /**
     * Set cookie name
     *
     * @param   string  $cookieName    The cookie name
     *
     * @return  Object  $this
     *
     * @throws \Comodojo\Exception\CookieException
     */
    public function setName($name) {

        if ( empty($name) OR !is_scalar($name) ) throw new CookieException("Invalid cookie name");

        $this->name = $name;

        return $this;

    }

    /**
     * Get cookie name
     *
     * @return  string
     */
    public function getName() {

        return $this->name;

    }

    /**
     * Set cookie content
     *
     * @param   mixed   $cookieValue    Cookie content
     * @param   bool    $serialize      If true (default) cookie will be serialized first
     *
     * @return  Object  $this
     *
     * @throws \Comodojo\Exception\CookieException
     */
    public function setValue($value, $serialize=true) {

        if ( !is_scalar($value) AND $serialize === false ) throw new CookieException("Cannot set non-scalar value without serialization");

        if ( $serialize ) {

            $cookie_value = serialize($value);

        } else {

            $cookie_value = $value;

        }

        if ( strlen($value) > 4096 ) throw new CookieException("Cookie size larger than 4KB");

        $this->value = $cookie_value;

        return $this;

    }

    /**
     * Get cookie content
     *
     * @param   bool    $unserializes    If true (default) cookie will be unserialized first
     *
     * @return  mixed
     */
    public function getValue($unserialize=true) {

        return $unserialize ? unserialize($this->value) : $this->value;

    }

    /**
     * Set cookie's expiration time
     *
     * @param   int     $timestamp
     *
     * @return  Object  $this
     *
     * @throws \Comodojo\Exception\CookieException
     */
    public function setExpire($timestamp) {

        if ( !is_int($timestamp) ) throw new CookieException("Invalud cookie's expiration time");

        $this->expire = $timestamp;

        return $this;

    }

    /**
     * Set cookie's path
     *
     * @param   string  $location
     *
     * @return  Object  $this
     *
     * @throws \Comodojo\Exception\CookieException
     */
    public function setPath($location) {

        if ( !is_string($location) ) throw new CookieException("Invalid path attribute");
        
        $this->path = $path;

        return $this;

    }

    /**
     * Set cookie's domain
     *
     * @param   string  $domain
     *
     * @return  Object   $this
     *
     * @throws \Comodojo\Exception\CookieException
     */
    public function setDomain($domain) {

        if ( !is_scalar($domain) OR !self::checkDomain($domain) ) throw new CookieException("Invalid domain attribute");

        $this->domain = $domain;

        return $this;

    }

    /**
     * Set if the cookie should be transmitted only via https
     *
     * @param   bool     $mode
     *
     * @return  Object   $this
     */
    public function setSecure($mode=false) {

        $this->secure = filter_var($mode, FILTER_VALIDATE_BOOLEAN);

        return $this;

    }

    /**
     * Set if cookie should be available only to HTTP protocol
     *
     * @param   bool     $mode
     *
     * @return  Object   $this
     */
    public function setHttponly($mode=false) {

        $this->httponly = filter_var($mode, FILTER_VALIDATE_BOOLEAN);

        return $this;

    }

    /**
     * Save cookie
     *
     * @return bool
     *
     * @throws \Comodojo\Exception\CookieException
     */
    public function save() {

        if ( setcookie(
            $this->name,
            $this->value,
            $this->expire,
            $this->path,
            $this->domain,
            $this->secure,
            $this->httponly
        ) === false ) throw new CookieException("Cannot set cookie: ".$this->name);

        return true;

    }

    /**
     * Load cookie content from request
     *
     * @return Object $this
     *
     * @throws \Comodojo\Exception\CookieException
     */
    public function load() {

        if ( !$this->exists($this->name) ) throw new CookieException("Cookie does not exists");

        $this->value = $_COOKIE[$this->name];

        return $this;

    }

    /**
     * Delete a cookie
     *
     * @return bool
     *
     * @throws \Comodojo\Exception\CookieException
     */
    public function delete() {

        if ( !$this->exists($this->name) ) return true;

        if ( setcookie(
            $this->name,
            null,
            time() - 86400,
            null,
            null,
            $this->secure,
            $this->httponly
        ) === false ) throw new CookieException("Cannot delete cookie");

        return true;

    }

    /**
     * Check if cookie exists
     *
     * @return  bool
     */
    public function exists() {

        return isset( $_COOKIE[$this->name] );

    }

    /**
     * Static method to create a cookie quickly
     *
     * @param   string   $name  The cookie name
     * 
     * @param   array    $properties    Array of properties cookie should have
     *
     * @return  Object \Comodojo\Cookies\Cookie
     *
     * @throws  \Comodojo\Exception\CookieException
     */
    static public function create($name, $properties=array(), $serialize=true) {

        try {

            $cookie = new Cookie($name);

            self::cookieProperties($cookie, $properties, $serialize);

        } catch (CookieException $ce) {
            
            throw $ce;

        }

        return $cookie;

    }

    /**
     * Static method to get a cookie quickly
     *
     * @param   string   $name  The cookie name
     *
     * @return  Object \Comodojo\Cookies\Cookie
     *
     * @throws  \Comodojo\Exception\CookieException
     */
    static public function retrieve($name) {

        try {

            $cookie = new Cookie($name);

            $return = $cookie->load();

        } catch (CookieException $ce) {
            
            throw $ce;

        }

        return $return;

    }

    /**
     * Static method to delete a cookie quickly
     *
     * @param   string   $name  The cookie name
     *
     * @return  Object \Comodojo\Cookies\Cookie
     *
     * @throws  \Comodojo\Exception\CookieException
     */
    static public function erase($name) {

        try {

            $cookie = new Cookie($name);

            $return = $cookie->delete();

        } catch (CookieException $ce) {
            
            throw $ce;

        }

        return $return;

    }

    /**
     * Set content of $cookie from array $properties
     *
     * @param   string   $name  The cookie name
     *
     * @param   array    $properties    Array of properties cookie should have
     *
     * @return  Object \Comodojo\Cookies\Cookie
     */
    static protected function cookieProperties(CookieInterface $cookie, $properties, $serialize) {

        foreach ($properties as $property => $value) {
                
            switch ($property) {

                case 'value':
                    
                    $cookie->setValue($value, $serialize);

                    break;

                case 'expire':

                    $cookie->setExpire($value);

                    break;

                case 'path':

                    $cookie->setPath($value);

                    break;

                case 'domain':

                    $cookie->setDomain($value);

                    break;

                case 'secure':

                    $cookie->setSecure($value);

                    break;

                case 'httponly':

                    $cookie->setHttponly($value);

                    break;

            }

        }

        return $cookie;

    }

    /**
     * Check if domain is valid
     *
     * Main code from: http://stackoverflow.com/questions/1755144/how-to-validate-domain-name-in-php
     *
     * @param   string   $domain_name  The domain name to check
     *
     * @return  bool
     */
    static protected function checkDomain($domain_name) {
    
        if ( $domain_name[0] == '.' ) $domain_name = substr($domain_name, 1);

        return ( preg_match("/^([a-z\d](-*[a-z\d])*)(\.([a-z\d](-*[a-z\d])*))*$/i", $domain_name) //valid chars check
                && preg_match("/^.{1,253}$/", $domain_name) //overall length check
                && preg_match("/^[^\.]{1,63}(\.[^\.]{1,63})*$/", $domain_name) ); //length of each label

    }

}
