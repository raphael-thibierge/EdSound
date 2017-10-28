// get react from dependencies
import React from 'react';
import ReactDOM from 'react-dom';
import 'bootstrap/dist/css/bootstrap.css';
import PlaylistRoot from './components/playlists/PlaylistRoot';

if (window.app){
    switch (window.app.name){
        case 'playlist.mobile':
            ReactDOM.render(
                React.createElement(PlaylistRoot, window.app.props),
                document.getElementById('app-playlist')
            );
            break;
    }
}

