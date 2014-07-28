<?php
class FileManager extends MY_Controller {
    private $error = array();
    public $files_path = '';

    public function __construct() {
        parent::__construct();
        $this->load->library('form_validation');
		$this->load->language('filemanager');
        $this->load->helper('utf8');
        $this->load->model('vault/document_model');
        $this->load->model('vault/tempfile_model');
        $this->config->set_item('replacer', array('enquiries' => array('enquiry|Enquiries')));
        $this->config->set_item('exclude', array('browse'));
        $this->files_path = $this->config->item('files_path') . 'vault/';
    }

	public function index() {

        $data['title'] = $this->lang->line('heading_title');


		$data['entry_folder'] = $this->lang->line('entry_folder');
		$data['entry_move'] = $this->lang->line('entry_move');
		$data['entry_copy'] = $this->lang->line('entry_copy');
		$data['entry_rename'] = $this->lang->line('entry_rename');

		$data['button_folder'] = $this->lang->line('button_folder');
		$data['button_delete'] = $this->lang->line('button_delete');
		$data['button_move'] = $this->lang->line('button_move');
		$data['button_copy'] = $this->lang->line('button_copy');
		$data['button_rename'] = $this->lang->line('button_rename');
		$data['button_upload'] = $this->lang->line('button_upload');
		$data['button_download'] = $this->lang->line('button_download');
		$data['button_refresh'] = $this->lang->line('button_refresh');
		$data['button_submit'] = $this->lang->line('button_submit');

		$data['error_select'] = $this->lang->line('error_select');
		$data['error_directory'] = $this->lang->line('error_directory');

		$data['directory'] = $this->files_path;

        $field = $this->input->get('field');

		if (isset($field)) {
			$data['field'] = $field;
		} else {
			$data['field'] = '';
		}

        $ckeditor = $this->input->get('CKEditorFuncNum');
		if (isset($ckeditor)) {
			$data['fckeditor'] = $ckeditor;
		} else {
			$data['fckeditor'] = false;
		}

        $data['jstoload'] = array(
            'jquery/tree.jquery',
            'browserplus',
            'plupload/plupload.full',
            'plupload/jquery.plupload.queue',
            'jquery/datatables/media/js/jquery.dataTables',
            'jquery/jquery.urlparser',
            'jquery/jquery.dynotable',
            'jquery/datatables/extras/ColumnFilterWidgets/media/js/ColumnFilterWidgets',
            'jquery/jquery-ui-timepicker-addon'
        );

        $data['csstoload'] = array('filemanager', 'jquery.plupload.queue', 'ColumnFilterWidgets', 'jqtree');
        $data['content_view'] = 'vault/filemanager';
        $this->load->view('template/default', $data);
	}

	public function directory() {
		$json = array();

			$directories = glob(rtrim($this->files_path . str_replace('../', '', $this->input->post('directory')), '/') . '/*', GLOB_ONLYDIR);

			if ($directories) {
				$i = 0;

				foreach ($directories as $directory) {
					$json[$i]['name'] = utf8_substr($directory, strlen($this->files_path));

					$children = glob(rtrim($directory, '/') . '/*', GLOB_ONLYDIR);

					if ($children)  {
						// $json[$i]['children'] = ' ';
					}

					$i++;
				}
			}

		echo json_encode($json);
	}

	public function files() {
        $directory = $this->input->post('directory');
        $files = $this->document_model->get_by_folder($directory);

        // Make fields more human-friendly
        foreach ($files as $key => $file) {
            $files[$key]['identity_human'] = get_lang_for_constant_value('VAULT_FILE_IDENTITY', $file['identity']);
            $files[$key]['type_human'] = get_lang_for_constant_value('VAULT_FILE_TYPE', $file['identity']);

            foreach ($file['versions'] as $version_number => $version) {
                $size = $version['file_size'];

                $i = 0;

                $suffix = array(
                    'B',
                    'KB',
                    'MB',
                    'GB',
                    'TB'
                );

                while (($size / 1024) > 1) {
                    $size = $size / 1024;
                    $i++;
                }

                $files[$key]['versions'][$version_number]['file_size_human'] = round(utf8_substr($size, 0, utf8_strpos($size, '.') + 4), 2) . $suffix[$i];

                $this->load->helper('date');
                $files[$key]['versions'][$version_number]['revision_date_human'] = mdate('%d-%m-%Y %h:%m %A', $version['revision_date']);
                $files[$key]['versions'][$version_number]['creation_date_human'] = mdate('%d-%m-%Y %h:%m %A', $version['creation_date']);
            }
        }

        echo $this->load->view('vault/files/list', array('files' => $files), true);
	}

    /**
     * Currently nested folders are not supported, so all new folders are created at the root node
     */
	public function create() {
		$this->load->language('filemanager');

		$json = array();
        $directory = '/';

        if (isset($directory)) {
            $name = $this->input->post('name');
			if (isset($name) || $name) {
				$directory = rtrim($this->files_path . str_replace('../', '', $this->input->post('directory')), '/');

				if (!is_dir($directory)) {
					$json['error'] = $this->lang->line('error_directory');
				}

				if (file_exists($directory . '/' . str_replace('../', '', $this->input->post('name')))) {
					$json['error'] = $this->lang->line('error_exists');
				}
			} else {
				$json['error'] = $this->lang->line('error_name');
			}
		} else {
			$json['error'] = $this->lang->line('error_directory');
		}

		if (!has_capability('vault:editfiles')) {
      		$json['error'] = $this->lang->line('error_permission');
    	}

		if (!isset($json['error'])) {
			mkdir($directory . '/' . str_replace('../', '', $this->input->post('name')), 0777);

			$json['success'] = $this->lang->line('text_create');
		}

		echo json_encode($json);
	}

	public function delete() {
		$this->load->language('filemanager');

		$json = array();

        $path = $this->input->post('path');
		if (isset($path)) {
			$path = rtrim($this->files_path . str_replace('../', '', html_entity_decode($this->input->post('path'), ENT_QUOTES, 'UTF-8')), '/');

			if (!file_exists($path)) {
				$json['error'] = $this->lang->line('error_select');
			}

			if ($path == rtrim($this->files_path, '/')) {
				$json['error'] = $this->lang->line('error_delete');
			}
		} else {
			$json['error'] = $this->lang->line('error_select');
		}

		if (!has_capability('vault:editfiles')) {
      		$json['error'] = $this->lang->line('error_permission');
    	}

		if (!isset($json['error'])) {
			if (is_file($path)) {
				unlink($path);
			} elseif (is_dir($path)) {
				$this->recursiveDelete($path);
			}

			$json['success'] = $this->lang->line('text_delete');
		}

		echo json_encode($json);
	}

	protected function recursiveDelete($directory) {
		if (is_dir($directory)) {
			$handle = opendir($directory);
		}

		if (!$handle) {
			return false;
		}

		while (false !== ($file = readdir($handle))) {
			if ($file != '.' && $file != '..') {
				if (!is_dir($directory . '/' . $file)) {
					unlink($directory . '/' . $file);
				} else {
					$this->recursiveDelete($directory . '/' . $file);
				}
			}
		}

		closedir($handle);

		rmdir($directory);

		return true;
	}

	public function move() {
		$this->load->language('filemanager');

		$json = array();
        $from = $this->input->post('from');
        $to = $this->input->post('to');
		if (isset($from) && isset($to)) {
			$from = rtrim($this->files_path . str_replace('../', '', html_entity_decode($this->input->post('from'), ENT_QUOTES, 'UTF-8')), '/');

			if (!file_exists($from)) {
				$json['error'] = $this->lang->line('error_missing');
			}

			if ($from == $this->config->item('files_path') . 'vault') {
				$json['error'] = $this->lang->line('error_default');
			}

			$to = rtrim($this->files_path . str_replace('../', '', html_entity_decode($this->input->post('to'), ENT_QUOTES, 'UTF-8')), '/');

			if (!file_exists($to)) {
				$json['error'] = $this->lang->line('error_move');
			}

			if (file_exists($to . '/' . basename($from))) {
				$json['error'] = $this->lang->line('error_exists');
			}
		} else {
			$json['error'] = $this->lang->line('error_directory');
		}

		if (!has_capability('vault:editfiles')) {
      		$json['error'] = $this->lang->line('error_permission');
    	}

		if (!isset($json['error'])) {
			rename($from, $to . '/' . basename($from));

			$json['success'] = $this->lang->line('text_move');
		}

		echo json_encode($json);
	}

	public function copy() {
		$this->load->language('filemanager');

		$json = array();

        $path = $this->input->post('path');
        $name = $this->input->post('name');
		if (isset($path) && isset($name)) {
			if ((utf8_strlen($this->input->post('name')) < 3) || (utf8_strlen($this->input->post('name')) > 255)) {
				$json['error'] = $this->lang->line('error_filename');
			}

			$old_name = rtrim($this->files_path . str_replace('../', '', html_entity_decode($this->input->post('path'), ENT_QUOTES, 'UTF-8')), '/');

			if (!file_exists($old_name) || $old_name == $this->config->item('files_path') . 'vault') {
				$json['error'] = $this->lang->line('error_copy');
			}

			if (is_file($old_name)) {
				$ext = strrchr($old_name, '.');
			} else {
				$ext = '';
			}

			$new_name = dirname($old_name) . '/' . str_replace('../', '', html_entity_decode($this->input->post('name'), ENT_QUOTES, 'UTF-8') . $ext);

			if (file_exists($new_name)) {
				$json['error'] = $this->lang->line('error_exists');
			}
		} else {
			$json['error'] = $this->lang->line('error_select');
		}

		if (!has_capability('vault:editfiles')) {
      		$json['error'] = $this->lang->line('error_permission');
    	}

		if (!isset($json['error'])) {
			if (is_file($old_name)) {
				copy($old_name, $new_name);
			} else {
				$this->recursiveCopy($old_name, $new_name);
			}

			$json['success'] = $this->lang->line('text_copy');
		}

		echo json_encode($json);
	}

	function recursiveCopy($source, $destination) {
		$directory = opendir($source);

		@mkdir($destination);

		while (false !== ($file = readdir($directory))) {
			if (($file != '.') && ($file != '..')) {
				if (is_dir($source . '/' . $file)) {
					$this->recursiveCopy($source . '/' . $file, $destination . '/' . $file);
				} else {
					copy($source . '/' . $file, $destination . '/' . $file);
				}
			}
		}

		closedir($directory);
	}

	public function folders() {
		echo $this->recursiveFolders($this->files_path);
	}

	protected function recursiveFolders($directory) {
		$output = '';

		$output .= '<option value="' . utf8_substr($directory, strlen($this->files_path)) . '">' . utf8_substr($directory, strlen($this->files_path)) . '</option>';

		$directories = glob(rtrim(str_replace('../', '', $directory), '/') . '/*', GLOB_ONLYDIR);

		foreach ($directories  as $directory) {
			$output .= $this->recursiveFolders($directory);
		}

		return $output;
	}

	public function rename() {
		$this->load->language('filemanager');

		$json = array();

        $path = $this->input->post('path');
        $name = $this->input->post('name');
		if (isset($path) && isset($name)) {
			if ((utf8_strlen($this->input->post('name')) < 3) || (utf8_strlen($this->input->post('name')) > 255)) {
				$json['error'] = $this->lang->line('error_filename');
			}

			$old_name = rtrim($this->files_path . str_replace('../', '', html_entity_decode($this->input->post('path'), ENT_QUOTES, 'UTF-8')), '/');

			if (!file_exists($old_name) || $old_name == $this->files_path) {
				$json['error'] = $this->lang->line('error_rename');
			}

			if (is_file($old_name)) {
				$ext = strrchr($old_name, '.');
			} else {
				$ext = '';
			}

			$new_name = dirname($old_name) . '/' . str_replace('../', '', html_entity_decode($this->input->post('name'), ENT_QUOTES, 'UTF-8') . $ext);

			if (file_exists($new_name)) {
				$json['error'] = $this->lang->line('error_exists');
			}
		}

		if (!has_capability('vault:editfiles')) {
      		$json['error'] = $this->lang->line('error_permission');
    	}

		if (!isset($json['error'])) {
			rename($old_name, $new_name);

			$json['success'] = $this->lang->line('text_rename');
		}

		echo json_encode($json);
	}

	public function upload($directory=null) {
        $this->load->language('filemanager');
        $this->load->library('plupload');
        $this->load->helper('url');

        if (empty($directory)) {
            $directory = '';
        }

        $this->plupload->target_folder .= "/$directory";
        $result = $this->plupload->process_upload($_REQUEST,$_FILES);
        $decoded_result = json_decode($result);

        // TODO
        // 5. Make sure temporary files are deleted from tempfile table after being recorded in vault_files

        // Record info about this file in a temporary table
        if (!empty($_FILES['file']) && empty ($_FILES['file']['error'])) {
            $file = $_FILES['file'];
            $this->load->model('vault/tempfile_model');
            $tempfile_data = array('original_name' => $file['name'],
                                   'file_size' => $file['size'],
                                   'new_name' => $decoded_result->result,
                                   'folder' => $directory);

            $this->tempfile_model->add($tempfile_data);
        }

        // We only upload the files to disk at this stage. Then we present the user with a form for extra info per file
		echo $result;
    }

    /**
     * Checks the vault_temp_files table, and displays a form with a file per row and field headings across the top
     */
    public function fileinfo() {
        $this->load->helper('date');
        $temp_files = $this->tempfile_model->get();

        foreach ($temp_files as $key => $file) {
            $temp_files[$key]->new_filename = mdate('%y%m%d%h%i', $file->creation_date) . $file->new_name;
        }

        $this->load->model('enquiries/enquiry_model');
        $identities = array(
                VAULT_FILE_IDENTITY_CUSTOMER => get_lang_for_constant_value('VAULT_FILE_IDENTITY', VAULT_FILE_IDENTITY_CUSTOMER),
                VAULT_FILE_IDENTITY_CS => get_lang_for_constant_value('VAULT_FILE_IDENTITY', VAULT_FILE_IDENTITY_CS)
            );
        $types = array(null => "-- Select one --",
                VAULT_FILE_TYPE_ENQUIRY => get_lang_for_constant_value('VAULT_FILE_TYPE', VAULT_FILE_TYPE_ENQUIRY),
                VAULT_FILE_TYPE_ORDER => get_lang_for_constant_value('VAULT_FILE_TYPE', VAULT_FILE_TYPE_ORDER)
            );
        $data = array('temp_files' => $temp_files,
                      'identities' => $identities,
                      'types' => $types);
        if (IS_AJAX) {
            echo $this->load->view('vault/fileinfo', $data, true);
        } else {
            $data['title'] = 'Vault file info';
            $data['jstoload'] = array(
                'jquery/jquery-ui-timepicker-addon'
            );

            $data['content_view'] = 'vault/fileinfo';
            $this->load->view('template/default', $data);

        }
    }

    public function record_documents() {
        $file_data = $this->upload->data();

        $this->load->model('vault/document_model');
        $new_file = array('document_type' => FILE_TYPE_ENQUIRY,
                          'filename_original' => $file_data['orig_name'],
                          'filename_new' => $file_data['file_name'],
                          'raw_name' => $file_data['raw_name'],
                          'location' => "enquiry/$enquiry_id/",
                          'file_type' => $file_data['file_type'],
                          'file_extension' => $file_data['file_ext'],
                          'file_size' => $file_data['file_size'],
                          'is_image' => $file_data['is_image'],
                          'image_width' => $file_data['image_width'],
                          'image_height' => $file_data['image_height'],
                          'image_type' => $file_data['image_type'],
                          'image_size_str' => $file_data['image_size_str']);
        if (!$file_id = $this->file_model->add($new_file)) {
            $json['error'] = 'The file was uploaded, but the file info could not be recorded in the database...';
        } else {
            $json['success'] = $this->lang->line('text_uploaded');
        }
    }

    function process_documents() {
        var_dump($this->input->post());
    }

    function search_enquiry_ids($term) {

    }
}
?>
