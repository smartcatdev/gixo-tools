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
                
                $query->the_post();
                $this->update_meta( get_the_ID(), array(
                    'descriptorID'  => $descriptorID,
                    'image_url'  => $data['image_url'],
                    'level'  => $data['level'],
                    'intensity'  => $data['intensity'],
                    'pace'  => $data['pace'],
                    'vibe'  => $data['vibe'],
                    'outdoor'  => $data['outdoor'],
                    'total_seconds'  => $data['total_seconds'],
                    'cam_on'  => $data['cam_on'],
                    'item_labels'  => $data['item_labels'],
                ) );
                
            }else {
                
                $id = wp_insert_post( array (
                    'post_title' => $data['title'],
                    'post_type' => 'session',
                    'post_status' => 'publish',
                    'post_content' => $data['description']
                ) );

                $this->update_meta( $id, array(
                    'descriptorID'  => $descriptorID,
                    'image_url'  => $data['image_url'],
                    'level'  => $data['level'],
                    'intensity'  => $data['intensity'],
                    'pace'  => $data['pace'],
                    'vibe'  => $data['vibe'],
                    'outdoor'  => $data['outdoor'],
                    'total_seconds'  => $data['total_seconds'],
                    'cam_on'  => $data['cam_on'],
                    'item_labels'  => $data['item_labels'],
                ) );            
                
            }
            
        }


        wp_reset_postdata();
    }

    private function update_meta( $id, $args ) {
        
        update_post_meta( $id, 'descriptorID', $args['descriptorID'] );                
        update_post_meta( $id, 'image_url', $args['image_url'] );                
        update_post_meta( $id, 'level', $args['level'] );                
        update_post_meta( $id, 'intensity', $args['intensity'] );                
        update_post_meta( $id, 'pace', $args['pace'] );                
        update_post_meta( $id, 'vibe', $args['vibe'] );                
        update_post_meta( $id, 'outdoor', $args['outdoor'] );                
        update_post_meta( $id, 'total_seconds', $args['total_seconds'] );                
        update_post_meta( $id, 'cam_on', $args['cam_on'] );                
        update_post_meta( $id, 'item_labels', implode(',', $args['item_labels'] ) );    
        
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
                'image_url'     => $descriptor->image_url,
                'level'     => $descriptor->level,
                'intensity'     => $descriptor->intensity,
                'pace'     => $descriptor->pace,
                'vibe'     => $descriptor->vibe,
                'outdoor'     => $descriptor->outdoor,
                'description'     => $descriptor->description,
                'total_seconds'     => $descriptor->total_seconds,
                'cam_on'     => $descriptor->cam_on,
                'item_labels'     => $descriptor->item_labels,
            );
        }

        return $descriptors;
    }

}
  


