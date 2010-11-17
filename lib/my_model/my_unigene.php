<?php
class my_unigene extends unigene
{
  # auto-generated constructor
  public function __construct($baseClassInstance)
  {
     $this->copy_to($baseClassInstance->copy_from());
  }

  # your code here
}
