<?php
include 'sidebar.php';
?>

<!-- HTML Content -->
<div class="main-panel">
    <div class="content-wrapper">
        <div class="row">
            <div class="col-md-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <iframe id="book-iframe" src="<?php echo htmlspecialchars($index_path, ENT_QUOTES, 'UTF-8'); ?>" style="width: 100%; height: 80vh;"></iframe>
                        <div class="navigation-controls">
                            <button id="prev-button" class="btn btn-primary">Previous</button>
                            <button id="next-button" class="btn btn-primary">Next</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- content-wrapper ends -->
    <?php include 'footer.html'; ?>
</div>

<!-- Include Font Awesome -->
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.1.1/css/all.min.css">

<!-- Include Bootstrap CSS -->
<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/5.1.3/css/bootstrap.min.css">

<!-- Custom CSS for landscape mode on mobile -->
<style>
    @media (max-width: 768px) {
        #book-iframe {
            width: 100vw;
            height: 100vh;
        }
    }
    .navigation-controls {
        margin-top: 10px;
        text-align: center;
    }
    .navigation-controls button {
        margin: 0 5px;
    }
</style>

<!-- Include SCORM API Wrapper JavaScript -->
<script src="/views/admin/JavaScript/SCORM_API_wrapper.js"></script>

<!-- SCORM Player Script -->
<script>
    (function () {
        let API = null;
        let version = null;

        const SCORM12 = {
            initialize: () => API.LMSInitialize(''),
            terminate: () => API.LMSFinish(''),
            getValue: (key) => API.LMSGetValue(key),
            setValue: (key, value) => API.LMSSetValue(key, value),
            commit: () => API.LMSCommit(''),
            getLastError: () => API.LMSGetLastError(),
            getErrorString: (code) => API.LMSGetErrorString(code)
        };

        const SCORM2004 = {
            initialize: () => API.Initialize(''),
            terminate: () => API.Terminate(''),
            getValue: (key) => API.GetValue(key),
            setValue: (key, value) => API.SetValue(key, value),
            commit: () => API.Commit(''),
            getLastError: () => API.GetLastError(),
            getErrorString: (code) => API.GetErrorString(code)
        };

        function findAPI(win) {
            while (!API && win) {
                if (win.API) {
                    version = '1.2';
                    return win.API;
                }
                if (win.API_1484_11) {
                    version = '2004';
                    return win.API_1484_11;
                }
                win = win.parent;
            }
            return null;
        }

        function initSCORM() {
            API = findAPI(window);

            if (!API) {
                console.error('SCORM API not found');
                return;
            }

            const scorm = version === '1.2' ? SCORM12 : SCORM2004;

            if (scorm.initialize() !== 'true') {
                console.error('Failed to initialize SCORM session');
                return;
            }

            console.log(`SCORM ${version} session initialized`);

            const learnerName = scorm.getValue('cmi.learner_name') || scorm.getValue('cmi.core.student_name');
            console.log(`Welcome, ${learnerName}`);

            const scormContentUrl = 'http://localhost//Unit1 AIML/index.html';
            const iframe = document.getElementById('book-iframe');
            iframe.src = scormContentUrl;
        }

        function terminateSCORM() {
            if (API) {
                const scorm = version === '1.2' ? SCORM12 : SCORM2004;

                if (scorm.terminate() !== 'true') {
                    console.error('Failed to terminate SCORM session');
                } else {
                    console.log('SCORM session terminated');
                }
            }
        }

        window.addEventListener('load', initSCORM);
        window.addEventListener('unload', terminateSCORM);
    })();
</script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        var scorm = pipwerks.SCORM;
        scorm.version = "1.2"; // or "2004"
        
        var success = scorm.init();
        if (success) {
            console.log("SCORM initialized successfully.");
        } else {
            console.log("SCORM initialization failed.");
        }

        var iframe = document.getElementById('book-iframe');
        iframe.addEventListener('beforeunload', function() {
            scorm.quit();
        });

        var currentPage = 0;
        var pages = [
            "<?php echo htmlspecialchars($index_path, ENT_QUOTES, 'UTF-8'); ?>",
            // Add more pages here
        ];

        document.getElementById('prev-button').addEventListener('click', function() {
            if (currentPage > 0) {
                currentPage--;
                iframe.src = pages[currentPage];
            }
        });

        document.getElementById('next-button').addEventListener('click', function() {
            if (currentPage < pages.length - 1) {
                currentPage++;
                iframe.src = pages[currentPage];
            }
        });
    });
</script>