<?php

namespace gixo;

add_action( 'init', function() {
    
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

        $sessions = $this->get_sessions();

        // Delete sessions that don't exist anymore
        $this->delete_sessions( $sessions );

        // Create/update valid sessions
        $this->sync_sessions( $sessions );

        delete_transient( 'gixo_doing_sync' );
    }

    private function sync_sessions( $sessions ) {
        
        foreach ( $sessions as $sessionID => $data ) {

            
            $query = new \WP_Query( array (
                'post_type' => 'session',
                'post_status' => 'publish',
                'meta_query' => array (
                    array (
                        'key' => 'sessionID',
                        'value' => $sessionID,
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

                update_post_meta( $id, 'sessionID', $sessionID );                
                update_post_meta( $id, 'duration', $data['duration'] );                
                
            }
            
        }


        wp_reset_postdata();
    }

    /**
     * 
     * Deletes sessions that are not longer valid
     * 
     * @param array $sessions
     */
    private function delete_sessions( $sessions ) {

        $query = new \WP_Query( array (
            'post_type' => 'session',
            'post_status' => 'publish',
            'meta_query' => array (
                array (
                    'key' => 'sessionID',
                    'value' => array_keys( $sessions ),
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
     * Runs on the gixo_get_sessions hourly cron
     * 
     * @action gixo_get_sessions
     * 
     */
    private function get_sessions() {

        $session_response = wp_remote_get( 'http://alpha.gixo.com/rest/sessions?query_type=all' );

        return $this->format_response( $session_response );
        
    }

    private function format_response( $data ) {

        $data = json_decode( wp_remote_retrieve_body( $data ) );

        if ( !$data ) {
            return;
        }

        $sessions = array ();

        foreach ( $data as $session ) {

            $sessions[ $session->sessionID ] = array(
                'title'     => $session->descriptor->title,
                'duration'  => $session->time->total_seconds
            );
        }

        return $sessions;
    }

}



