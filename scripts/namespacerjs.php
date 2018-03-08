<?php
//chdir('/var/www/trunk');

//find all PHP files except updates.php and updates.inc.php because we shouldn't touch them
$cmd = 'find . -type f -name "*.js"';
exec($cmd, $scripts, $return_var);

//return var should be 0 otherwise something went wrong
if($return_var!=0)
    exit("Find command did not run successfully.\n");

foreach($scripts as $script){


    //get the contents of the PHP Script
    $content = $oldContent = file_get_contents($script);

    //All GO classes are build up like GO_Module_SomeClass so we can match them with
    //a regular expression.
//    $regex = '/[^A-Za-z0-9_-](GO(_[A-Za-z0-9]+)+)\b/';
    $regex = '/[^A-Za-z0-9_-](GO(\\\\\\\\[A-Za-z0-9]+)+)\b/';

    $classes = array();

    if(preg_match_all($regex, $content, $matches))
    {
        //loop through the matched class names and store the old classname as key
        //and the new classname as value
        foreach($matches[1] as $className){

            //skip all uppercase classnames. They are old eg. GO_USERS, GO_LINKS
            if($className!=strtoupper($className)){
                if(!in_array($className, $classes)){
                    $classes[$className]=str_replace('\\\\','_', $className);
                }
            }
        }

//        var_dump($classes);

        //replace all old class names with the new namespaced ones.
        foreach($classes as $oldClassName=>$newClassName){
            $content = str_replace($oldClassName, $newClassName, $content);
        }
    }


    //if the contents were modified then write them to the file.
    if($oldContent != $content){
        echo "\nReplacing $script\n";
        file_put_contents($script, $content);
    }




}

echo "All done!\n";

