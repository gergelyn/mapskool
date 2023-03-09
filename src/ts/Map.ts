class MapSkool {
    private mapElement: HTMLDivElement;
    private datas: any;
    private map: L.Map;
    private markers: L.Marker<any>[] = [];
    private socket: WebSocket | undefined;

    constructor() {
        this.mapElement = document.getElementById("map") as HTMLDivElement;
        this.map = L.map("map").setView([47.53, 21.6391], 13);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; <a href="http://www.openstreetmap.org/copyright">OpenStreetMap</a>'
        }).addTo(this.map);

        let host = "ws://localhost:12345/websockets.php";
        this.socket = new WebSocket(host);

        this.socket.onopen = function(e) {
            console.log("Connection established!");
        }

        this.socket.onmessage = async (e: MessageEvent<any>) => {
            console.log(e);
            const data = await JSON.parse(e.data);
            this.datas = data;
            await this.drawMarkers();
        }
    }
    
    private async drawMarkers(): Promise<void> {
        if (this.markers.length > 0) {
            this.markers.forEach((marker: L.Marker<any>) => {
                this.map.removeLayer(marker);
                this.markers.splice(this.markers.indexOf(marker), 1);
            });
        }

        this.datas.forEach((data: any) => {
            if (data.GPS !== null) {
                const gps = data.GPS.split(',');
                const marker = L.marker(gps).addTo(this.map);
                let markerStr = "";
                for (const [key, value] of Object.entries(data)) {
                    if (value !== null && key !== null) {
                        if (key.toLowerCase().includes("telefon")) {
                            markerStr += `<b>${key}</b><br><a href="tel:${value}">${value}</a><br>`;
                        } else if ((value as string).toLowerCase().includes("@")) {
                            markerStr += `<b>${key}</b><br><a href="mailto:${value}">${value}</a><br>`;
                        } else {
                            markerStr += `<b>${key}</b><br>${value}<br>`;
                        }
                    }
                }
                marker.bindPopup(markerStr);
                this.markers.push(marker);
            }
        });
    }
}
