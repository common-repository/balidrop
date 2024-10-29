<?php

class Balidrop_UpImagesApi {

    public static $additional_image_sizes = [
        'ads-thumb'  => [ 'width' => 50, 'height' => 50, 'crop' => true ],
        'ads-medium' => [ 'width' => 220, 'height' => 220, 'crop' => true ],
        'ads-big'    => [ 'width' => 350, 'height' => 350, 'crop' => true ],
        'ads-large'  => [ 'width' => 640, 'height' => 640, 'crop' => true ],
    ];

    private $image_sizes = [ 'ads-thumb', 'ads-medium', 'ads-big', 'ads-large' ];

    private $generate = false;

    private function getAttachImageId( $likeTitleNameFile ) {

        global $wpdb;

        $filename = str_replace(strrchr($likeTitleNameFile, "."),"",$likeTitleNameFile);
        $attachment = $wpdb->get_col(
            $wpdb->prepare(
                "SELECT ID FROM {$wpdb->posts} WHERE post_name LIKE '%s' AND post_type='attachment';",'%' . $wpdb-> esc_like( $filename ) . '%'
            )
        );

        return  $attachment[0];
    }

    private function loadImages( $post_id, $data , $filename, $name, $replace = false) {

        $uploaddir  = wp_upload_dir();
        $uploadfile = $uploaddir[ 'path' ] . '/' . $filename;

        if ( ! file_exists( $uploadfile ) || $replace) {

            $contents = $this->getContentData($data);
            if($contents == false)
                return false;

            $savefile = fopen( $uploadfile, 'w' );
            fwrite( $savefile, $contents );
            fclose( $savefile );
        }

        if ( !file_exists( $uploadfile ) ) {
            return false;
        }

        if ( filesize( $uploadfile ) == 0 ) {
            unlink($uploadfile);
            return false;
        }

        $wp_filetype = wp_check_filetype( basename( $filename ), null );

        $name = apply_filters( 'media_attachment_title', $name, $post_id );

        $attachment_data = [
            'post_mime_type' => $wp_filetype[ 'type' ],
            'post_title'     => $name,
            'post_content'   => '',
            'post_status'    => 'inherit'
        ];

        $attach_id = wp_insert_attachment( $attachment_data, $uploadfile, $post_id );

        $this->generateAttachment( $attach_id, $name );

        update_post_meta( $attach_id, '_wp_attachment_image_alt', $name );

        return $attach_id;
    }

    private function getContentData($data){

        if(substr($data, 0,2) == '//'){
            $data = 'http:'. $data;
        }

        if(substr($data, 0,4) == 'http'){
            return $this->file_get_contents($data);
        }

        return base64_decode($data);
    }

    private function file_get_contents( $file ) {

        $response = wp_remote_get( $file, [
            'timeout'   => 15,
            'sslverify' => false
        ] );

        if ( ! is_wp_error( $response ) || wp_remote_retrieve_response_code( $response ) === 200 ){
            return $response['body'];
        }

        return false;
    }

    private function getImageById( $id, $size = 'thumbnail' ) {

        $img = wp_get_attachment_image_src( $id, $size, false );

        if ( $img ) {
            return $img[ 0 ];
        }

        return false;
    }

    public function generateAttachment( $attach_id, $name = false, $image_sizes = [ 'ads-thumb', 'ads-medium', 'ads-big', 'ads-large' ] ) {

        $this->generate = true;

        $this->image_sizes  = array_merge( [ 'thumbnail' ], $image_sizes );
        $additional_image_sizes = [];

        foreach( $this->image_sizes as $key ) {

            if( isset( self::$additional_image_sizes[ $key ] ) ) {

                $additional_image_sizes[ $key ] = self::$additional_image_sizes[ $key ];
            }
        }

        global $_wp_additional_image_sizes;

        $imagenew     = get_post( $attach_id );
        $fullsizepath = get_attached_file( $imagenew->ID );

        if( ! function_exists( 'wp_generate_attachment_metadata' ) ) {
            include( ABSPATH . 'wp-admin/includes/image.php' );
        }

        $_temp_wp_additional_image_sizes = wp_get_additional_image_sizes();
        $_wp_additional_image_sizes      = $additional_image_sizes;

        $attach_data = \wp_generate_attachment_metadata( $attach_id, $fullsizepath );

        if( $name ) {
            $attach_data[ 'image_meta' ][ 'title' ] = $name;
        }

        \wp_update_attachment_metadata( $attach_id, $attach_data );

        $_wp_additional_image_sizes = $_temp_wp_additional_image_sizes;

        $this->generate = false;
    }

    /**
     * Upload Image by URL and Attach its to post by post ID
     *
     * @param $post_id - post_id. The images will attached to ths post
     * @param $name - title for images (to 'alt' tag)
     * @param $url - url of image
     * @param $size - return image url for this size ('thumbnail' || 'medium' || 'large' || 'full' || etc.)
     *
     * @return mixed
     */
    public function attachmentImage($post_id, $url, $size = 'full', $name = '')
    {

        $url = preg_replace('/(_\d+x\d+\.jpe?g)/','',$url);
        preg_match('/[^\?]+\.(jpe?g|jpe|gif|png)\b/i', $url, $matches);
        if (!$matches) {
            return false;
        }

        $p = strrpos($url, ".");
        $r = substr($url, $p);

        if ($name != '') {
            $filename = $post_id . '-' . sanitize_title($name) . $r;
        } else {
            $filename = $post_id . '-' . sanitize_title(md5($url)) . $r;
            $name = basename($filename);
        }

        $attach_id = $this->getAttachImageId(basename($filename));

        if (empty($attach_id)) {
            $attach_id = $this->loadImages($post_id, $url, $filename, $name);
        }

        return [
            'url' => $this->getImageById($attach_id, $size),
            'id' => $attach_id
        ];
    }

}
