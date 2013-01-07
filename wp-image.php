<?php

class WP_Image {
	private $filepath;
	private $attachment_id;

	private $editor;

	private $metadata;


	public function __construct( $attachment_id ) {
		if( wp_attachment_is_image( $attachment_id ) ) {
			$filepath = get_attached_file( $attachment_id );

			if ( $filepath && file_exists( $filepath ) ) {
				if ( 'full' != $size && ( $data = image_get_intermediate_size( $attachment_id, $size ) ) ) {
					$filepath = apply_filters( 'load_image_to_edit_filesystempath', path_join( dirname( $filepath ), $data['file'] ), $attachment_id, $size );

					$this->filepath      = apply_filters( 'load_image_to_edit_path', $filepath, $attachment_id, 'full' );
					$this->attachment_id = $attachment_id;
				}
			}
		}
	}

	public function add_image_size( $name, $max_w, $max_h, $crop = false, $force = false ) {
		$editor = $this->get_editor();
		$this->get_metadata();

		if( $force == false && isset( $this->metadata['sizes'][ $name ] ) )
			return new WP_Error( 'image_size_exists', __( 'This image size already exists' ) );

		if( is_wp_error( $editor ) )
			return $editor;

		$editor->resize( $max_w, $max_h, $crop );
		$result = $editor->save();

		unset( $result['path'] );
		$this->metadata['sizes'][ $name ] = $result;

		return $this->update_metadata();
	}

	private function get_editor() {
		if( ! isset( $this->editor ) )
			$this->editor = wp_get_image_editor( $this->filepath );

		return $this->editor;
	}

	private function get_metadata() {
		if( ! isset( $this->metadata ) )
			$this->metadata = wp_get_attachment_metadata( $this->attachment_id );

		return $this->metadata; 
	}

	private function update_metadata() {
		if( $this->metadata )
			return wp_update_attachment_metadata( $this->attachment_id, $this->metadata );

		return false;
	}
}