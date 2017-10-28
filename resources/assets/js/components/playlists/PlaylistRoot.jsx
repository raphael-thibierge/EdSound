import React from 'react';
import SongRender from '../songs/SongRender'

export default class PlaylistRoot extends React.Component {

    constructor(props) {
        super(props);
        this.state = {
            playlist: null,
            loaded: false,
        }
    }

    componentDidMount(){

        // load playlist data in ajax
        $.get(this.props.ajax_route)
            .catch(error => {
                alert('Failed to load playlist...');
                console.error(error);
            })
            .then(responseJSON => {
                if (responseJSON.status === 'success'){
                    // get response data
                    const data = responseJSON.data;
                    this.setState({
                        loaded: true,
                        playlist: data.playlist,
                    });
                }
            });
    }


    render(){

        if (this.state.loaded === false ){
            return (
                <p>Loading...</p>
            )
        }

        const playlist = this.state.playlist;

        return (
            <div className="row">
                <div className="col-xs-12">
                    {playlist.songs.map((track) => (
                        <SongRender track={track} key={track._id['$oid']}/>
                    ))}
                </div>
            </div>
        );
    }

}
