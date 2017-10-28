import React from 'react';
import { Media } from 'reactstrap';

export default class SongRender extends React.Component {

    constructor(props) {
        super(props);
    }

    humanDuration(duration_ms){
        let duration = Math.floor(duration_ms/1000);
        let seconds = Math.floor(duration%60);
        let minutes = Math.floor((duration-seconds)/60);
        return "(" + minutes + ':' + seconds + ')';
    }

    render(){

        const track = this.props.track;
        const spotifyTrack = track.spotify_data;
        console.log(spotifyTrack);

        const imageSrc = spotifyTrack.album.images[2].url;
        const name = spotifyTrack.name;
        const artist = spotifyTrack.artists[0].name;
        const duration = spotifyTrack.duration_ms;

        return (
            <Media>
                <Media left href="#">
                    <Media object src={imageSrc} alt="Generic placeholder image" />
                </Media>
                <Media body>
                    <Media heading>
                        {name} {this.humanDuration(duration)}
                    </Media>
                    {artist}
                </Media>
            </Media>
        );
    }

}
