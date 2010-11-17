<?php
class my_datasets_breeders extends datasets_breeders
{
  # auto-generated constructor
  public function __construct($baseClassInstance)
  {
     $this->copy_to($baseClassInstance->copy_from());
  }

  # your code here
}
