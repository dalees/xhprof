<?php

// by default assume that xhprof_html & xhprof_lib directories
// are at the same level.
$GLOBALS['XHPROF_LIB_ROOT'] = dirname(__FILE__) . '/../xhprof_lib';

require_once $GLOBALS['XHPROF_LIB_ROOT'].'/display/xhprof.php';
require_once $GLOBALS['XHPROF_LIB_ROOT']."/utils/xhprof_lib.php";
require_once $GLOBALS['XHPROF_LIB_ROOT']."/utils/xhprof_runs.php";

function save_run( $xhprof_data, $namespace='defaultns' ) {
	$output_dir = "/var/tmp/xhprof";
	if (!is_dir($output_dir)) {
		mkdir($output_dir, 0775);
	}
	//
	// Saving the XHProf run
	// using the default implementation of iXHProfRuns.
	//
	
	$xhprof_runs = new XHProfRuns_Default();
	
	// Save the run under a namespace "xhprof_foo".
	//
	// **NOTE**:
	// By default save_run() will automatically generate a unique
	// run id for you. [You can override that behavior by passing
	// a run id (optional arg) to the save_run() method instead.]
	//
	$run_id = $xhprof_runs->save_run($xhprof_data, $namespace);
	
	$url = "http://xhprof-viewer/index.php?run=$run_id&source=$namespace";
	echo "<pre>\n";
	echo "---------------\n".
	     "Data submitted!\n".
	     "The XHProf run can be viewed at \n".
	     "<a href='$url'>$url</a>\n";
	echo "</pre>\n";
}

function print_form() {
	echo "<html><head><title>Save xhprof run</title></head>\n";
	echo "<body>";
	echo "<h2>Submit new xhprof data</h2>\n";
	echo "<p>Documentation: <a href='https://wiki.wgtn.cat-it.co.nz/wiki/Xhprof#Site_independent'>Catalyst Wiki</a></p>\n";
	echo "<form method='post'>\n";
	echo "Namespace: <input name='namespace' type='text' value='xhprof_foo' /><br />\n";
	echo "<textarea row=4 cols=50 name='data'></textarea>";
	echo "<input type='submit' />\n";
	echo "</form>\n";
}

if ($_SERVER['REQUEST_METHOD'] == 'POST')
{	
	echo "<h1>submitted data</h1>\n";
	// sanity check and unpack the data
	$post_data = trim($_POST['data']);
	// strip all newlines, tabs, spaces
	$post_data = str_replace(array("\r", "\n", "\t", " "), '', $post_data);
	// remove any xhprof_data= at the start
	$post_data = str_replace('xhprof_data=', '', $post_data);
	echo "<p>".substr($post_data, 0, 50)."</p>\n";

	// pack hex to binary
	$packed = pack("H*", $post_data);
	#var_dump($packed);

	// uncompress, unserialise
	$array_data = unserialize(gzuncompress($packed));

	#print "<pre>";
	#print_r($array_data);
	#print "</pre>\n";
	save_run($array_data, $_POST['namespace']);
}
else
{
	print_form();
}
