<?php
if(file_exists(\GO::config()->file_storage_path.'customcss/javascript.js'))
		echo '<script type="text/javascript">'.file_get_contents(\GO::config()->file_storage_path.'customcss/javascript.js').'</script>'."\n";