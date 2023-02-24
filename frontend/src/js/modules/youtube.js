const ytElements = document.querySelectorAll('.youtube_video_single');

if (ytElements) {
    ytElements.forEach(ytElement => {
        const ytButton = ytElement.querySelector('.play-yt-video');
        const ytPlaceholder = ytElement.querySelector('.yt-placeholder');

        ytButton.addEventListener('click', (e) => {
            const ytIframe = ytElement.getElementsByTagName("iframe")[0];
            const ytIframeDataSrc = ytIframe.dataset.src;

            ytIframe.src = ytIframeDataSrc.replace("autoplay=0", "autoplay=1");
            ytPlaceholder.style.display = 'none';

            e.preventDefault()
        })
    })
}
