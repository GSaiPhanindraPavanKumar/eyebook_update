<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Privacy Policy - Knowbots</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        primary: '#4B49AC',
                        'primary-hover': '#3f3e91',
                    },
                    fontFamily: {
                        sans: ['"Plus Jakarta Sans"', 'sans-serif'],
                    },
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@200;300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        .fancy-button {
            position: relative;
            overflow: hidden;
            transition: all 700ms ease;
            z-index: 1;
            text-decoration: none;
        }
        
        .fancy-button::before {
            content: "";
            position: absolute;
            left: -50px;
            top: 0;
            width: 0;
            height: 100%;
            background-color: #4B49AC;
            transform: skewX(45deg);
            z-index: -1;
            transition: width 700ms ease;
        }
        
        .fancy-button:hover::before {
            width: 250%;
        }
        
        .fancy-button:hover {
            color: white;
            transform: scale(1.05);
            box-shadow: 4px 5px 17px -4px rgba(75, 73, 172, 0.3);
            text-decoration: none;
        }
        
        .login-button {
            background: linear-gradient(to right, #4B49AC, #6366F1);
            transition: all 700ms ease;
            text-decoration: none;
        }
        
        .login-button:hover {
            transform: scale(1.05);
            box-shadow: 4px 5px 17px -4px rgba(75, 73, 172, 0.4);
            text-decoration: none;
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="fixed w-full top-0 z-50 bg-white/80 backdrop-blur-lg border-b border-gray-200/30 transition-all duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-20">
                <!-- Logo Section -->
                <div class="flex items-center space-x-4">
                    <a href="/" class="flex items-center space-x-3">
                        <img src="https://i.ibb.co/7xL13b10/knowbots-logo.png" alt="Knowbots Logo" class="h-10 w-auto">
                        <div>
                            <h1 class="text-2xl font-bold bg-gradient-to-r from-primary to-[#6366F1] bg-clip-text text-transparent">
                                Knowbots
                            </h1>
                            <p class="text-xs text-gray-500">Learning Platform</p>
                        </div>
                    </a>
                </div>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center space-x-6">
                    <!-- Back to Home -->
                    <a href="/" 
                       class="fancy-button no-underline px-5 py-2 rounded-full font-medium text-sm text-gray-700
                                 border border-primary/30 hover:border-primary">
                        <span class="flex items-center">
                            <svg class="w-4 h-4 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                            </svg>
                            Back to Home
                        </span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-28 pb-12">
        <h1 class="text-3xl font-bold text-gray-900 mb-8">Privacy Policy</h1>
        <div class="bg-white rounded-xl shadow-sm p-8">
            <!-- Search Bar -->
            <div class="mb-8">
                <div class="relative">
                    <input type="text" id="policySearch" 
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-primary/20 focus:border-primary"
                           placeholder="Search privacy policy...">
                    <span class="absolute right-3 top-2.5 text-gray-400">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                                  d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                        </svg>
                    </span>
                </div>
            </div>

            <!-- Policy Content -->
            <div id="policyContent" class="space-y-8">
                <!-- Introduction -->
                <section>
                    <p class="text-lg text-gray-600 mb-8">Your privacy is important to us. This privacy statement explains the personal data Knowbots processes, how Knowbots processes it, and for what purposes.</p>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900">1. Information Collection and Use</h2>
                    <div class="mt-4 space-y-4 text-gray-600">
                        <p>We collect information to provide better services to all our users. We collect information in the following ways:</p>
                        <div class="ml-4 space-y-6">
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">1.1 Personal Data</h3>
                                <ul class="list-disc ml-6 mt-2 space-y-2">
                                    <li><strong>Information you give us:</strong> When you sign up for a Knowbots Account, we collect personal information including your name, email address, telephone number, and payment information when applicable.</li>
                                    <li><strong>Educational Information:</strong> Your educational background, institution details, and academic credentials.</li>
                                    <li><strong>Account Information:</strong> Login credentials, profile data, and account preferences.</li>
                                    <li><strong>Payment Details:</strong> When you make purchases, we collect payment information through our secure payment processors.</li>
                                </ul>
                            </div>
                            <div>
                                <h3 class="text-lg font-medium text-gray-900">1.2 Usage Data</h3>
                                <ul class="list-disc ml-6 mt-2 space-y-2">
                                    <li><strong>Service Usage:</strong> Information about how you use our services, including course interactions, assessment completions, and learning patterns.</li>
                                    <li><strong>Technical Data:</strong> Device information, IP addresses, log data, and browser type.</li>
                                    <li><strong>Performance Data:</strong> Assessment results, progress tracking, and learning analytics.</li>
                                    <li><strong>Interaction Data:</strong> Comments, feedback, and communications with other users.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900">2. How We Use Information</h2>
                    <div class="mt-4 space-y-4 text-gray-600">
                        <p>We use the information we collect from all our services to:</p>
                        <ul class="list-disc ml-6 space-y-2">
                            <li>Provide, maintain, protect, and improve our services</li>
                            <li>Develop new features and functionality</li>
                            <li>Protect Knowbots and our users</li>
                            <li>Personalize your learning experience</li>
                            <li>Analyze and improve educational outcomes</li>
                            <li>Communicate updates and important information</li>
                        </ul>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900">3. Information Sharing</h2>
                    <div class="mt-4 space-y-4 text-gray-600">
                        <p>We do not share personal information with companies, organizations, or individuals outside of Knowbots unless one of the following circumstances applies:</p>
                        <ul class="list-disc ml-6 space-y-2">
                            <li><strong>With your consent:</strong> We will share personal information with companies, organizations, or individuals outside of Knowbots when we have your explicit consent.</li>
                            <li><strong>For external processing:</strong> We provide personal information to our affiliates or trusted partners to process it for us, based on our instructions and in compliance with our Privacy Policy.</li>
                            <li><strong>For legal reasons:</strong> We will share information if we have a good-faith belief that access, use, preservation, or disclosure is reasonably necessary to comply with applicable laws or regulations.</li>
                        </ul>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900">4. Data Protection</h2>
                    <div class="mt-4 space-y-3 text-gray-600">
                        <p>We implement robust security measures to protect your personal information:</p>
                        <ul class="list-disc ml-4 space-y-2">
                            <li>End-to-end encryption for sensitive data</li>
                            <li>Regular security audits and updates</li>
                            <li>Strict access controls and authentication</li>
                            <li>Compliance with educational data protection standards</li>
                            <li>Regular backup and disaster recovery procedures</li>
                            <li>Employee training on data protection and privacy</li>
                        </ul>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900">5. Your Rights</h2>
                    <div class="mt-4 space-y-3 text-gray-600">
                        <p>You have the right to:</p>
                        <ul class="list-disc ml-4 space-y-2">
                            <li>Access your personal data</li>
                            <li>Request data correction or deletion</li>
                            <li>Opt-out of certain data processing</li>
                            <li>Receive a copy of your data</li>
                            <li>Object to processing of your data</li>
                            <li>Withdraw consent at any time</li>
                        </ul>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900">6. Policy Updates</h2>
                    <div class="mt-4 space-y-4 text-gray-600">
                        <p>Our Privacy Policy may change from time to time. We will not reduce your rights under this Privacy Policy without your explicit consent. We will:</p>
                        <ul class="list-disc ml-6 space-y-2">
                            <li>Post any privacy policy changes on this page</li>
                            <li>Notify you of significant changes via email</li>
                            <li>Maintain previous versions for your review</li>
                            <li>Obtain consent when required by law</li>
                        </ul>
                    </div>
                </section>

                <section>
                    <h2 class="text-2xl font-semibold text-gray-900">7. Contact Us</h2>
                    <div class="mt-4 space-y-4 text-gray-600">
                        <p>If you have any questions about this Privacy Policy or our data practices, please contact us:</p>
                        <ul class="list-none ml-6 space-y-2">
                            <li>Email: privacy@knowbots.com</li>
                            <li>Phone: (555) 123-4567</li>
                            <li>Address: [Your Company Address]</li>
                        </ul>
                    </div>
                </section>
            </div>
        </div>
    </main>

    <script>
        // Search Functionality
        document.getElementById('policySearch').addEventListener('input', function(e) {
            const searchText = e.target.value.toLowerCase();
            const content = document.getElementById('policyContent');
            const textNodes = [];
            
            function getTextNodes(node) {
                if (node.nodeType === 3) {
                    textNodes.push(node);
                } else {
                    for (let child of node.childNodes) {
                        getTextNodes(child);
                    }
                }
            }
            
            getTextNodes(content);
            
            const highlights = content.querySelectorAll('mark');
            highlights.forEach(h => {
                const parent = h.parentNode;
                parent.replaceChild(document.createTextNode(h.textContent), h);
                parent.normalize();
            });
            
            if (searchText) {
                textNodes.forEach(node => {
                    const text = node.textContent;
                    const index = text.toLowerCase().indexOf(searchText);
                    
                    if (index >= 0) {
                        const before = text.substring(0, index);
                        const match = text.substring(index, index + searchText.length);
                        const after = text.substring(index + searchText.length);
                        
                        const highlight = document.createElement('mark');
                        highlight.textContent = match;
                        highlight.style.backgroundColor = '#4B49AC20';
                        highlight.style.color = '#4B49AC';
                        highlight.style.padding = '0 2px';
                        highlight.style.borderRadius = '2px';
                        
                        const fragment = document.createDocumentFragment();
                        fragment.appendChild(document.createTextNode(before));
                        fragment.appendChild(highlight);
                        fragment.appendChild(document.createTextNode(after));
                        
                        node.parentNode.replaceChild(fragment, node);
                    }
                });
            }
        });
    </script>
</body>
</html> 