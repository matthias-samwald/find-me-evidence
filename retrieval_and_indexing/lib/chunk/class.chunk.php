<?php
/**
 * Chunk
 * 
 * Reads a large file in as chunks for easier parsing.
 * 
 * The chunks returned are whole <$this->options['element']/>s found within file.
 * 
 * Each call to read() returns the whole element including start and end tags.
 * 
 * Tested with a 1.8MB file, extracted 500 elements in 0.11s
 * (with no work done, just extracting the elements)
 * 
 * Usage:
 * <code>
 *   // initialize the object
 *   $file = new Chunk('chunk-test.xml', array('element' => 'Chunk'));
 *   
 *   // loop through the file until all lines are read
 *   while ($xml = $file->read()) {
 *     // do whatever you want with the string
 *     $o = simplexml_load_string($xml);
 *   }
 * </code>
 * 
 * @package default
 * @author Dom Hastings
 */
class Chunk {
  /**
   * options
   *
   * @var array Contains all major options
   * @access public
   */
  public $options = array(
    'path' => './',       // string The path to check for $file in
    'element' => '',      // string The XML element to return
    'chunkSize' => 512    // integer The amount of bytes to retrieve in each chunk
  );
  
  /**
   * file
   *
   * @var string The filename being read
   * @access public
   */
  public $file = '';
  /**
   * pointer
   *
   * @var integer The current position the file is being read from
   * @access public
   */
  public $pointer = 0;
  
  /**
   * handle
   *
   * @var resource The fopen() resource
   * @access private
   */
  private $handle = null;
  /**
   * reading
   *
   * @var boolean Whether the script is currently reading the file
   * @access private
   */
  private $reading = false;
  /**
   * readBuffer
   * 
   * @var string Used to make sure start tags aren't missed
   * @access private
   */
  private $readBuffer = '';
  
  /**
   * __construct
   * 
   * Builds the Chunk object
   *
   * @param string $file The filename to work with
   * @param array $options The options with which to parse the file
   * @author Dom Hastings
   * @access public
   */
  public function __construct($file, $options = array()) {
    // merge the options together
    $this->options = array_merge($this->options, (is_array($options) ? $options : array()));
    
    // check that the path ends with a /
    if (substr($this->options['path'], -1) != '/') {
      $this->options['path'] .= '/';
    }
    
    // normalize the filename
    $file = basename($file);
    
    // make sure chunkSize is an int
    $this->options['chunkSize'] = intval($this->options['chunkSize']);
    
    // check it's valid
    if ($this->options['chunkSize'] < 64) {
      $this->options['chunkSize'] = 512;
    }
    
    // set the filename
    $this->file = realpath($this->options['path'].$file);
    
    // check the file exists
    if (!file_exists($this->file)) {
      throw new Exception('Cannot load file: '.$this->file);
    }
    
    // open the file
    $this->handle = fopen($this->file, 'r');
    
    // check the file opened successfully
    if (!$this->handle) {
      throw new Exception('Error opening file for reading');
    }
  }
  
  /**
   * __destruct
   * 
   * Cleans up
   *
   * @return void
   * @author Dom Hastings
   * @access public
   */
  public function __destruct() {
    // close the file resource
    fclose($this->handle);
  }
  
  /**
   * read
   * 
   * Reads the first available occurence of the XML element $this->options['element']
   *
   * @return string The XML string from $this->file
   * @author Dom Hastings
   * @access public
   */
  public function read() {
    // check we have an element specified
    if (!empty($this->options['element'])) {
      // trim it
      $element = trim($this->options['element']);
      
    } else {
      $element = '';
    }
    
    // initialize the buffer
    $buffer = false;
    
    // if the element is empty
    if (empty($element)) {
      // let the script know we're reading
      $this->reading = true;
      
      // read in the whole doc, cos we don't know what's wanted
      while ($this->reading) {
        $buffer .= fread($this->handle, $this->options['chunkSize']);
        
        $this->reading = (!feof($this->handle));
      }
      
      // return it all
      return $buffer;
      
    // we must be looking for a specific element
    } else {
      // set up the strings to find
      $open = '<'.$element.'>';
      $close = '</'.$element.'>';
      
      // let the script know we're reading
      $this->reading = true;
      
      // reset the global buffer
      $this->readBuffer = '';
      
      // this is used to ensure all data is read, and to make sure we don't send the start data again by mistake
      $store = false;
      
      // seek to the position we need in the file
      fseek($this->handle, $this->pointer);
      
      // start reading
      while ($this->reading && !feof($this->handle)) {
        // store the chunk in a temporary variable
        $tmp = fread($this->handle, $this->options['chunkSize']);
        
        // update the global buffer
        $this->readBuffer .= $tmp;
        
        // check for the open string
        $checkOpen = strpos($tmp, $open);
        
        // if it wasn't in the new buffer
        if (!$checkOpen && !($store)) {
          // check the full buffer (in case it was only half in this buffer)
          $checkOpen = strpos($this->readBuffer, $open);
          
          // if it was in there
          if ($checkOpen) {
            // set it to the remainder
            $checkOpen = $checkOpen % $this->options['chunkSize'];
          }
        }
        
        // check for the close string
        $checkClose = strpos($tmp, $close);
        
        // if it wasn't in the new buffer
        if (!$checkClose && ($store)) {
          // check the full buffer (in case it was only half in this buffer)
          $checkClose = strpos($this->readBuffer, $close);
          
          // if it was in there
          if ($checkClose) {
            // set it to the remainder plus the length of the close string itself
            $checkClose = ($checkClose + strlen($close)) % $this->options['chunkSize'];
          }
          
        // if it was
        } elseif ($checkClose) {
          // add the length of the close string itself
          $checkClose += strlen($close);
        }
        
        // if we've found the opening string and we're not already reading another element
        if ($checkOpen !== false && !($store)) {
          // if we're found the end element too
          if ($checkClose !== false) {
            // append the string only between the start and end element
            $buffer .= substr($tmp, $checkOpen, ($checkClose - $checkOpen));
            
            // update the pointer
            $this->pointer += $checkClose;
            
            // let the script know we're done
            $this->reading = false;
            
          } else {
            // append the data we know to be part of this element
            $buffer .= substr($tmp, $checkOpen);
            
            // update the pointer
            $this->pointer += $this->options['chunkSize'];
            
            // let the script know we're gonna be storing all the data until we find the close element
            $store = true;
          }
          
        // if we've found the closing element
        } elseif ($checkClose !== false) {
          // update the buffer with the data upto and including the close tag
          $buffer .= substr($tmp, 0, $checkClose);
          
          // update the pointer
          $this->pointer += $checkClose;
          
          // let the script know we're done
          $this->reading = false;
          
        // if we've found the closing element, but half in the previous chunk
        } elseif ($store) {
          // update the buffer
          $buffer .= $tmp;
          
          // and the pointer
          $this->pointer += $this->options['chunkSize'];
        }
      }
    }
    
    // return the element (or the whole file if we're not looking for elements)
    return $buffer;
  }
}
