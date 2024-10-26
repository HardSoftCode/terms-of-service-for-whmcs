<?php

use Illuminate\Database\Capsule\Manager as Capsule;
use WHMCS\Input\Sanitize;

if (!defined("WHMCS")) die("This file cannot be accessed directly");

function TermsOfService_config() 
{
  return [
    "name" => "Terms of Service",
    "description" => "The module will give you the ability to create your terms of service page with multi languages",
    "version" => "2.3.0",
    "author" => "<a href=\"http://www.hardsoftcode.com\" target=\"_blank\">HSCode</a>",
    "language" => "english",
    "fields" => [
    "delete" => ["FriendlyName" => "Delete Module DB", "Type" => "yesno", "Size" => "25", "Description" => "Tick this box to delete the module database on deactivating"],
  ]];
}

function TermsOfService_activate() 
{
  try 
  {
    if(!Capsule::schema()->hasTable('mod_termsofservice'))
    {
      Capsule::schema()->create('mod_termsofservice', function ($table) 
      {
        $table->increments('id');
        $table->text('title');
        $table->text('contents');
        $table->integer('parentid');
        $table->text('language');
        $table->text('status')->nullable();
        $table->integer('orders');
       });
     }

     if(!Capsule::schema()->hasTable('mod_tosconfog'))
     {
       Capsule::schema()->create('mod_tosconfog', function ($table) 
       {
         $table->text('setting');
         $table->text('value');
       });
        
       Capsule::table('mod_tosconfog')->insert(
       [
          ['setting' => 'Description', 'value' => ''],
          ['setting' => 'Keyword', 'value' => ''],
       ]);
     }
 
  }
  catch (\Exception $e) 
  {
    return ['status'=>'error','description'=>'Unable to create table: ' .$e->getMessage()];
  }

  
  return ['status'=>'success','description'=>'Module activated successfully. Click configuration to configure the module'];
}

function TermsOfService_deactivate()
{
  $delete = Capsule::table('tbladdonmodules')->where('module', 'TermsOfService')->where('setting', 'delete')->first();

  if($delete->value)
  {
    try 
    {
      Capsule::schema()->dropIfExists('mod_termsofservice'); 
      Capsule::schema()->dropIfExists('mod_tosconfog'); 
    } 
    catch (\Exception $e) 
    {
      return ['status'=>'error','description'=>'Unable to drop tables: ' .$e->getMessage()];
    }
  }
  
  return ['status'=>'success','description'=>'Module deactivated successfully'];
}

function TermsOfService_output($vars) 
{
  global $CONFIG;
  
  $modulelink = $vars['modulelink'];
  $version    = $vars['version'];
  $LANG       = $vars['_lang'];

  $SLSETTINGS = [];
  $results = Capsule::table('mod_tosconfog')->get();
  foreach ($results as $result)
  {
    $setting = $result->setting;
    $value   = $result->value;
    $SLSETTINGS[$setting] = $value;
  }
  
  require(dirname( __FILE__ ).'/includes/pages/home.php');
}

function TermsOfService_clientarea($vars) 
{
  $LANG = $vars['_lang'];

  $result = Capsule::table('mod_termsofservice')->where('language', '')->where('status', NULL)->orderBy('orders', 'ASC')->get();
  foreach ($result as $data)
  {
    $id       = $data->id;
    $title    = $data->title;
    $contents = Sanitize::decode($data->contents);
    $orders   = $data->orders;

    $pdata = Capsule::table('mod_termsofservice')->where('language', $_SESSION['Language'])->where('parentid', $id)->where('status', NULL)->orderBy('orders', 'ASC')->first();
    
    if($pdata->title)
    {
      $title = $pdata->title;
    }
    
    if($pdata->contents)
    {
      $contents = Sanitize::decode($pdata->contents);
    }
    
	  $terms[] = ['id' => $id, 'title' => $title, 'contents' => $contents, 'orders' => $orders];	       
  }  
  
  return [
        'pagetitle' => $LANG['termsofservice'],
        'breadcrumb' => ['index.php?m=TermsOfService' => $LANG['termsofservice']],
        'templatefile' => 'templates/homepage',
        'requirelogin' => false, #true or false
        'vars' => [
                        'TSLANG' => $LANG,
                        'terms'  => $terms,
                        ],
  ];
}
