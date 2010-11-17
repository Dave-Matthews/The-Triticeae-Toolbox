<?php
class my_users extends users
{
  # auto-generated constructor
  public function __construct($baseClassInstance)
  {
     $this->copy_to($baseClassInstance->copy_from());
  }

  # your code here

}
