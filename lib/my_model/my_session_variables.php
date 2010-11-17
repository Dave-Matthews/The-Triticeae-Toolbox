<?php
class my_session_variables extends session_variables
{
  # auto-generated constructor
  public function __construct($baseClassInstance)
  {
     $this->copy_to($baseClassInstance->copy_from());
  }

  # your code here
}
