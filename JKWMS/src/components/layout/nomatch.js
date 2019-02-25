import React, { PureComponent } from 'react';
export default class Home extends PureComponent {
    render() {
        return (
            <div style={{ display: 'flex', width: '100%', justifyContent: 'center', alignItems: 'center' }}>
                <div style={{width: '60%',padding:'15%',margin:'8%', background: "url(" + require("./src/nomatch.jpg") + ") no-repeat 0 0",backgroundSize: 'cover' }}>
                    
                </div>
            </div>
        )
    }
}