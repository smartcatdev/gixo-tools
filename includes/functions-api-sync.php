<?php

namespace gixo;

add_action( HOOK, function() {
    
    SyncService::instance();
    
});

Class SyncService {

    use Singleton;

    function initialize() {
        $this->do_sync();
    }

    private function do_sync() {

        if ( get_transient( 'gixo_doing_sync' ) ) {
            return;
        }

        set_transient( 'gixo_doing_sync', true, 60 );

        $descriptors = $this->get_descriptors();

        // Delete descriptors that don't exist anymore
        $this->delete_descriptors( $descriptors );

        // Create/update valid descriptors
        $this->sync_descriptors( $descriptors );

        delete_transient( 'gixo_doing_sync' );
    }

    private function sync_descriptors( $descriptors ) {
        
        foreach ( $descriptors as $descriptorID => $data ) {

            
            $query = new \WP_Query( array (
                'post_type' => 'session',
                'post_status' => 'publish',
                'meta_query' => array (
                    array (
                        'key' => 'descriptorID',
                        'value' => $descriptorID,
                        'compare' => '='
                    )
                ),
            ) );
            
            if( $query->have_posts() ) {
                continue;
            }else {
                
                $id = wp_insert_post( array (
                    'post_title' => $data['title'],
                    'post_type' => 'session',
                    'post_status' => 'publish'
                ) );

                update_post_meta( $id, 'descriptorID', $descriptorID );                
                update_post_meta( $id, 'image_url', $data['image_url'] );                
                update_post_meta( $id, 'level', $data['level'] );                
                update_post_meta( $id, 'intensity', $data['intensity'] );                
                update_post_meta( $id, 'pace', $data['pace'] );                
                update_post_meta( $id, 'vibe', $data['vibe'] );                
                update_post_meta( $id, 'outdoor', $data['outdoor'] );                
                
            }
            
        }


        wp_reset_postdata();
    }

    /**
     * 
     * Deletes descriptors that are not longer valid
     * 
     * @param array $descriptors
     */
    private function delete_descriptors( $descriptors ) {

        $query = new \WP_Query( array (
            'post_type' => 'session',
            'post_status' => 'publish',
            'meta_query' => array (
                array (
                    'key' => 'descriptorID',
                    'value' => array_keys( $descriptors ),
                    'compare' => 'NOT IN'
                )
            ),
        ) );

        if ( $query->have_posts() ) {

            foreach ( $query->posts as $post ) {
                wp_delete_post( $post->ID, true );
            }
        }

        wp_reset_postdata();
    }

    /**
     * 
     * Runs on the gixo_get_descriptors hourly cron
     * 
     * @action gixo_get_descriptors
     * 
     */
    private function get_descriptors() {

        $descriptor_response = wp_remote_get( 'https://alpha.gixo.com/rest/sessions/descriptors' );

        return $this->format_response( $descriptor_response );
        
    }

    private function format_response( $data ) {

        $data = json_decode( wp_remote_retrieve_body( $data ) );

        if ( !$data ) {
            return;
        }

        $descriptors = array ();

        foreach ( $data as $descriptor ) {

            $descriptors[ $descriptor->descriptorID ] = array(
                'title'     => $descriptor->title,
                'image_url'     => $descriptor->image_url
            );
        }

        return $descriptors;
    }

}



