document.addEventListener('DOMContentLoaded', () => {
    const postGrid = document.getElementById('post-grid');
    const apiUrl = '/api/posts.php'; // Relative path if served from root
    const DEFAULT_THUMBNAIL = 'images/default-thumbnail.png';

    // Fetch posts
    let currentPage = 1;
    const limit = 6;
    const paginationContainer = document.createElement('div');
    paginationContainer.id = 'pagination';
    paginationContainer.className = 'pagination';
    postGrid.parentNode.insertBefore(paginationContainer, postGrid.nextSibling);

    function fetchPosts(page = 1) {
        fetch(`${apiUrl}?page=${page}&limit=${limit}`)
            .then(response => {
                if (!response.ok) throw new Error('Network response was not ok');
                return response.json();
            })
            .then(data => {
                renderPosts(data.data);
                renderPagination(data.meta);
                currentPage = page;
                window.scrollTo(0, 0);
            })
            .catch(error => {
                console.error('Error fetching posts:', error);
                postGrid.innerHTML = '<p class="error-message">게시물을 불러오는데 실패했습니다.</p>';
            });
    }

    // Initial fetch
    fetchPosts();

    function renderPagination(meta) {
        paginationContainer.innerHTML = '';

        const totalPages = meta.totalPages;
        const current = meta.page;
        const pageGroupSize = 10;

        const currentGroup = Math.ceil(current / pageGroupSize);
        const startPage = (currentGroup - 1) * pageGroupSize + 1;
        const endPage = Math.min(startPage + pageGroupSize - 1, totalPages);

        // Prev Button
        if (startPage > 1) {
            const prevBtn = createPageBtn('Prev', startPage - 1);
            paginationContainer.appendChild(prevBtn);
        }

        // Page Numbers
        for (let i = startPage; i <= endPage; i++) {
            const btn = createPageBtn(i, i);
            if (i === current) btn.classList.add('active');
            paginationContainer.appendChild(btn);
        }

        // Next Button
        if (endPage < totalPages) {
            const nextBtn = createPageBtn('Next', endPage + 1);
            paginationContainer.appendChild(nextBtn);
        }
    }

    function createPageBtn(label, pageNum) {
        const btn = document.createElement('button');
        btn.textContent = label;
        btn.className = 'page-btn';
        btn.addEventListener('click', () => fetchPosts(pageNum));
        return btn;
    }

    function renderPosts(posts) {
        postGrid.innerHTML = ''; // Clear loading state

        posts.forEach(post => {
            const card = document.createElement('article');
            card.className = 'card';

            card.innerHTML = `
                <a href="api/templates/view.php?id=${post.seq}" style="display: block; text-decoration: none; color: inherit;">
                    <div class="card-image-wrapper">
                        <img src="${post.image_url ? (post.image_url.startsWith('/') ? post.image_url : 'uploads/' + post.image_url) : DEFAULT_THUMBNAIL}" alt="${post.title}" class="card-image" loading="lazy">
                        <span class="badge" data-category="${post.category}">${post.category}</span>
                    </div>
                </a>
                <div class="card-content">
                    <a href="api/templates/view.php?id=${post.seq}" style="text-decoration: none; color: inherit;">
                        <h2 class="card-title">${post.title}</h2>
                    </a>
                    <p class="card-summary">${post.summary}</p>
                    <div class="card-footer">
                        <span class="author-avatar"></span>
                        <div class="footer-info">
                            <span class="author-name">${post.author}</span>
                            <span class="post-date">${post.date}</span>
                        </div>
                        <button class="icon-btn" aria-label="Share">
                            <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="18" cy="5" r="3"/><circle cx="6" cy="12" r="3"/><circle cx="18" cy="19" r="3"/><line x1="8.59" y1="13.51" x2="15.42" y2="17.49"/><line x1="15.41" y1="6.51" x2="8.59" y2="10.49"/></svg>
                        </button>
                    </div>
                </div>
            `;

            // Share Button Logic
            const shareBtn = card.querySelector('.icon-btn');
            shareBtn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation(); // Prevent triggering card click if any

                // 배포 시 URL 주소 바꾸기
                const shareUrl = new URL(`api/templates/view.php?id=${post.seq}`, window.location.href).href;

                navigator.clipboard.writeText(shareUrl).then(() => {
                    alert('주소가 복사되었습니다.\n' + shareUrl);
                }).catch(err => {
                    console.error('URL copy failed:', err);
                    alert('주소 복사에 실패했습니다.');
                });
            });

            postGrid.appendChild(card);
        });
    }

    // View Toggle Logic
    const btnGrid = document.getElementById('btn-grid');
    const btnList = document.getElementById('btn-list');

    // Load saved preference
    const savedView = localStorage.getItem('viewMode');
    if (savedView === 'list') {
        enableListView();
    }

    btnGrid.addEventListener('click', () => {
        postGrid.classList.remove('list-layout');
        btnGrid.classList.add('active');
        btnList.classList.remove('active');
        localStorage.setItem('viewMode', 'grid');
    });

    btnList.addEventListener('click', enableListView);

    function enableListView() {
        postGrid.classList.add('list-layout');
        btnList.classList.add('active');
        btnGrid.classList.remove('active');
        localStorage.setItem('viewMode', 'list');
    }
});
