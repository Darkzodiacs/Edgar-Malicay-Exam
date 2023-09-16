   new Vue({
        el: '#channelList',
        data: {
            channels: [],
            selectedChannel: null,
            channelDataUrl: '',
            showChannels: false,
            channelData: {
                channel_info: {
                    title: '',
                    description: '',
                    thumbnail: ''
                },
                videos: []
            },
            currentPage: 1,
            videosPerPage: 20,
            selectedVideo: null
        },
        methods: {
            fetchChannels() {
                axios.get('youtube_channel_json.php')
                    .then(response => {
                        this.channels = response.data; 
                    })
                    .catch(error => {
                        console.error(error);
                    });
            },
			fetchData() {
                    const channelId = document.getElementById('inputField').value;
                   
                    fetch('sync_youtube_channel.php', {
                        method: 'POST',
                        body: JSON.stringify({ channelId }),
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                       
                       alert(data.message);
					   location.reload();
                    })
                    .catch(error => {
                          alert("An error occurred or invalid Channel ID! ");
                          location.reload();
                    });
                },
            fetchChannelData(channel) {
			 $("#channels").hide();
			 $("#backbtn,#app").show();
          

                axios.get(`youtube_channel_json.php?channel_id=${channel.channel_id}`)
                    .then(response => {
                        this.channelData = response.data;
                    })
                    .catch(error => {
                        console.error(error);
                    });
            },
            backtoch() {
             $("#channels").show();
			 $("#backbtn,#app").hide();
            },
			previousPage() {
                if (this.currentPage > 1) {
                    this.currentPage--;
                }
            },
            nextPage() {
                const totalPages = Math.ceil(this.channelData.videos.length / this.videosPerPage);
                if (this.currentPage < totalPages) {
                    this.currentPage++;
                }
            },
            playVideo(video) {
                this.selectedVideo = video;
            },
            toggleChannels() {
                this.showChannels = !this.showChannels;
            },
            getVideoThumbnailUrl(videoId) {
                return `https://img.youtube.com/vi/${videoId}/0.jpg`;
            },
            getVideoEmbedUrl(videoId) {
                return `https://www.youtube.com/embed/${videoId}?autoplay=1`;
            }
        },
        computed: {
            paginatedVideos() {
                const start = (this.currentPage - 1) * this.videosPerPage;
                const end = start + this.videosPerPage;
                return this.channelData.videos.slice(start, end);
            },
            totalPages() {
                return Math.ceil(this.channelData.videos.length / this.videosPerPage);
            }
        },
        created() {
            this.fetchChannels(); 
        }
    });

