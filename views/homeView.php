<!DOCTYPE html>
<html lang="en-US" dir="ltr">

  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">


    <!-- ===============================================-->
    <!--    Document Title-->
    <!-- ===============================================-->
    <title>Knowbots</title>


    <!-- ===============================================-->
    <!--    Favicons-->
    <!-- ===============================================-->
    <link rel="apple-touch-icon" sizes="180x180" href="/views/public/assets/images/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/public/assets/images/favicons/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/public/assets/imgages/favicons/favicon-16x16.png">
    <link rel="shortcut icon" type="image/x-icon" href="/public/assets/img/favicons/favicon.ico">
    <link rel="manifest" href="/public/assets/img/favicons/manifest.json">
    <meta name="msapplication-TileImage" content="public/assets/img/favicons/mstile-150x150.png">
    <meta name="theme-color" content="#ffffff">


    <!-- ===============================================-->
    <!--    Stylesheets-->
    <!-- ===============================================-->
    <link href="/views/landing/assets/css/theme.css" rel="stylesheet" />

    <!-- Tailwind and Custom Fonts -->
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


  <body>

    <!-- ===============================================-->
    <!--    Main Content-->
    <!-- ===============================================-->
    <main class="main" id="top">
      <!-- Modern Navbar -->
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
              <!-- Register Button -->
              <a href="register_student" 
                 class="fancy-button no-underline px-5 py-2 rounded-full font-medium text-sm text-gray-700
                        border border-primary/30
                        hover:border-primary">
                Register
              </a>
              
              <!-- Login Button -->
              <a href="login" 
                 class="login-button no-underline inline-flex items-center px-5 py-2 rounded-full font-medium text-sm text-white 
                        focus:outline-none focus:ring-2 focus:ring-primary/50">
                Login
                <svg class="ml-2 -mr-1 w-5 h-5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
              </a>
            </div>

            <!-- Mobile Menu Button -->
            <div class="md:hidden">
              <button type="button" class="mobile-menu-button inline-flex items-center justify-center p-2 rounded-md text-gray-700 
                      hover:text-primary hover:bg-gray-100 focus:outline-none focus:ring-2 focus:ring-inset focus:ring-primary/50 
                      transition-all duration-300 ease-in-out">
                <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                  <path class="menu-icon" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                  <path class="close-icon hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
              </button>
            </div>
          </div>
        </div>

        <!-- Mobile Menu -->
        <div class="mobile-menu hidden md:hidden bg-white border-t border-gray-200/30">
          <div class="px-2 pt-2 pb-3 space-y-1">
            <a href="register_student" 
               class="fancy-button no-underline block mx-3 px-5 py-2 rounded-full text-sm font-medium text-gray-700
                      border border-primary/30 text-center
                      hover:border-primary">
              Register
            </a>
            <div class="px-3 py-3">
              <a href="login" 
                 class="login-button no-underline block w-full px-5 py-2 text-center text-sm font-medium text-white
                        rounded-full">
                Login
              </a>
            </div>
          </div>
        </div>
      </nav>


      <!-- ============================================-->
      <!-- <section> begin ============================-->
      <section class="py-5">

        <div class="container">
          <div class="row align-items-center">
            <div class="col-md-5 col-lg-7 order-md-1 pt-8"><img class="img-fluid" src="/views/landing/assets/img/user1.png" alt="" /></div>
            <div class="col-md-7 col-lg-5 text-center text-md-start pt-5 pt-md-9">
              <h1 class="mb-4 display-2 fw-bold">Welcome to the LMS <br class="d-block d-lg-none d-xl-block" />Let's start</h1>
              <p class="mt-3 mb-4">Elevate Your Learning Experience <br />Access a World of Knowledge at Your Fingertips<br />Empower Yourself with Lifelong Learning. <br /></p>
            </div>
          </div>
        </div>
        <!-- end of .container-->

      </section>
      <!-- <section> close ============================-->
      <!-- ============================================-->




      <!-- ============================================-->
      <!-- <section> begin ============================-->
      <section class="py-4">

        <div class="container">
          <div class="row">
            <div class="col-12">
              <div class="card mb-3 bg-soft-danger rounded-3">
                <div class="row g-0 align-items-center">
                  <div class="col-md-5 col-lg-6 text-md-center"><img class="img-fluid" src="/views/landing/assets/img/illustrations/about.png" alt="" /></div>
                  <div class="col-md-7 col-lg-6 px-md-2 px-xl-6 text-center text-md-start">
                    <div class="card-body px-4 py-5 p-lg-3 p-md-4">
                      <h1 class="mb-4 fw-bold">Highly professional LMS features<br class="d-md-none d-xxl-block" /></h1>
                      <p class="card-text">Our LMS offers a comprehensive learning experience. With personalized learning paths, you can tailor your journey to your specific goals. Engage with interactive content, including videos, quizzes, and simulations, to enhance your understanding. Collaborate with fellow learners and experts through our vibrant community forums. Track your progress in real-time and stay motivated. Access our platform from any device, anytime, anywhere. Finally, validate your skills and showcase your achievements with certifications and badges.<br class="d-none d-xxl-block" /> <br class="d-none d-xxl-block" /> <br class="d-none d-xxl-block" /> <br class="d-none d-xxl-block" /> <br class="d-none d-xxl-block" /> </p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
        <!-- end of .container-->

      </section>
      <!-- <section> close ============================-->
      <!-- ============================================-->


      <section class="py-6">
        <div class="container-lg">
          <div class="row flex-center mb-5">
            <div class="col-auto text-center my-4">
              <h1 class="mb-4 fw-bold">Features</h1>
              <p>Our LMS offers personalized learning paths, interactive content, collaborative learning, real-time progress tracking, seamless access, and certification to empower your learning journey. <br /></p>
            </div>
          </div>
          <div class="row">
            <div class="col-md-6 mb-4">
              <div class="card px-5 px-md-3 py-lg-5">
                <div class="row flex-center">
                  <div class="bg-holder z-index-1 d-none d-lg-block" style="background-image:url(/views/landing/assets/img/illustrations/feature-bg-1.png);background-position:center;background-size:contain;">
                  </div>
                  <!--/.bg-holder-->

                  <div class="bg-holder z-index-1 d-block d-lg-none" style="background-image:url(/views/landing/assets/img/illustrations/feature-bg-1.png);background-position:center;background-size:cover;">
                  </div>
                  <!--/.bg-holder-->

                  <div class="col-md-4 pe-0 pe-md-0 text-center"><img class="img-fluid" src="/views/landing/assets/img/illustrations/feature-search.png" alt="" /></div>
                  <div class="col-md-8 ps-md-3 pe-md-2 text-center text-md-start z-index-2">
                    <div class="card-body px-0">
                      <h4 class="card-title pt-md-5">Personalized Learning</h4>
                      <p class="mb-0">Tailor your learning experience to your specific needs and goals <br class="d-none d-lg-block"> Our LMS adapts to your learning style and pace, providing you with customized content and recommendations. <br class="d-none d-lg-block">  <br class="d-none d-lg-block">  <br class="d-none d-lg-block"> <br class="d-none d-lg-block"> </p>
                      <div><button class="btn btn-lg ps-0 pe-3 learn-more-btn" type="button">Learn more
                        <svg class="bi bi-arrow-right" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#9C69E2" viewBox="0 0 16 16">
                          <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"></path>
                        </svg></button></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-4">
              <div class="card px-5 px-md-3 py-lg-5">
                <div class="row flex-center">
                  <div class="bg-holder z-index-1 d-none d-lg-block" style="background-image:url(/views/landing/assets/img/illustrations/feature-bg-2.png);background-position:center;background-size:contain;">
                  </div>
                  <!--/.bg-holder-->

                  <div class="bg-holder z-index-1 d-block d-lg-none" style="background-image:url(/views/landing/assets/img/illustrations/feature-bg-2.png);background-position:center;background-size:cover;">
                  </div>
                  <!--/.bg-holder-->

                  <div class="col-md-4 pe-0 pe-md-0 text-center"><img class="img-fluid" src="/views/landing/assets/img/illustrations/feature-hour.png" alt="" /></div>
                  <div class="col-md-8 ps-md-3 pe-md-2 text-center text-md-start z-index-2">
                    <div class="card-body px-0">
                      <h4 class="card-title pt-md-5">Interactive Content </h4>
                      <p class="mb-0">Engage with a variety of learning materials, including videos, quizzes, and simulations <br class="d-none d-lg-block"> Our interactive content makes learning fun and effective. <br class="d-none d-lg-block">  <br class="d-none d-lg-block">  <br class="d-none d-lg-block"> <br class="d-none d-lg-block"> </p>
                      <div><button class="btn btn-lg ps-0 pe-3 learn-more-btn" type="button">Learn more
                        <svg class="bi bi-arrow-right" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#9C69E2" viewBox="0 0 16 16">
                          <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"></path>
                        </svg></button></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-4">
              <div class="card px-5 px-md-3 py-lg-5">
                <div class="row flex-center">
                  <div class="bg-holder z-index-1 d-none d-lg-block" style="background-image:url(/views/landing/assets/img/illustrations/feature-bg-3.png);background-position:center;background-size:contain;">
                  </div>
                  <!--/.bg-holder-->

                  <div class="bg-holder z-index-1 d-block d-lg-none" style="background-image:url(/views/landing/assets/img/illustrations/feature-bg-3.png);background-position:center;background-size:cover;">
                  </div>
                  <!--/.bg-holder-->

                  <div class="col-md-4 pe-0 pe-md-0 text-center"><img class="img-fluid" src="/views/landing/assets/img/illustrations/feature-print.png" alt="" /></div>
                  <div class="col-md-8 ps-md-3 pe-md-2 text-center text-md-start z-index-2">
                    <div class="card-body px-0">
                      <h4 class="card-title pt-md-5">Collaborative Learning</h4>
                      <p class="mb-0">Connect with fellow learners and experts to share knowledge and solve problems together <br class="d-none d-lg-block"> Our platform fosters a supportive learning community where you can collaborate on projects, discuss ideas, and learn from each other. <br class="d-none d-lg-block">  <br class="d-none d-lg-block">  <br class="d-none d-lg-block"> <br class="d-none d-lg-block"> </p>
                      <div><button class="btn btn-lg ps-0 pe-3 learn-more-btn" type="button">Learn more
                        <svg class="bi bi-arrow-right" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#9C69E2" viewBox="0 0 16 16">
                          <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"></path>
                        </svg></button></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="col-md-6 mb-4">
              <div class="card px-5 px-md-3 py-lg-5">
                <div class="row flex-center">
                  <div class="bg-holder z-index-1 d-none d-lg-block" style="background-image:url(/views/landing/assets/img/illustrations/feature-bg-4.png);background-position:center;background-size:contain;">
                  </div>
                  <!--/.bg-holder-->

                  <div class="bg-holder z-index-1 d-block d-lg-none" style="background-image:url(/views/landing/assets/img/illustrations/feature-bg-4.png);background-position:center;background-size:cover;">
                  </div>
                  <!--/.bg-holder-->

                  <div class="col-md-4 pe-0 pe-md-0 text-center"><img class="img-fluid" src="/views/landing/assets/img/illustrations/feature-security.png" alt="" /></div>
                  <div class="col-md-8 ps-md-3 pe-md-2 text-center text-md-start z-index-2">
                    <div class="card-body px-0">
                      <h4 class="card-title pt-md-5">Certification and Badges</h4>
                      <p class="mb-0">Showcase your achievements and validate your skills with our certification and badging system <br class="d-none d-lg-block"> Earn digital badges to recognize your accomplishments and share them with your professional network. <br class="d-none d-lg-block">  <br class="d-none d-lg-block">  <br class="d-none d-lg-block"> <br class="d-none d-lg-block"> </p>
                      <div><button class="btn btn-lg ps-0 pe-3 learn-more-btn" type="button">Learn more
                        <svg class="bi bi-arrow-right" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#9C69E2" viewBox="0 0 16 16">
                          <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"></path>
                        </svg></button></div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </section>
      <section class="py-5">
        <div class="container-lg bg-info p-5 p-md-5 p-xl-7 rounded-3">
          <div class="row flex-center">
            <div class="col-12">
              <h2 class="text-light fw-bold">Testimonials</h2>
            </div>
          </div>
          <div class="carousel slide pt-6" id="carouselExampleDark" data-bs-ride="carousel">
            <div class="carousel-inner">
              <div class="carousel-item active" data-bs-interval="10000">
                <div class="row h-100">
                  <div class="col-md-4 mb-3 mb-md-0">
                    <div class="card h-100 py-3">
                      <div class="card-body my-2">
                        <div class="d-flex align-items-center"><img class="img-fluid me-3 me-md-2 me-lg-3" src="/views/landing/assets/img/gallery/1.png" width="70" alt="" />
                          <div class="flex-1 align-items-center">
                            <h6 class="mb-0 fs--1 text-1000 fw-medium"> The Beginner </h6>
                            <p class="fs--2 fw-normal text-info mb-0"></p>
                          </div>
                        </div>
                        <p class="card-text ps-7 ps-md-0 ps-xl-7 pt-md-4 pt-lg-3 pt-xl-0">"As a complete novice, I was initially intimidated by the technology. However, the LMS platform's user-friendly interface and clear instructions made it easy to navigate. The interactive courses and supportive community helped me gain confidence and master the skills I needed."</p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4 mb-3 mb-md-0">
                    <div class="card h-100 py-3">
                      <div class="card-body my-2">
                        <div class="d-flex align-items-center"><img class="img-fluid me-3 me-md-2 me-lg-3" src="/views/landing/assets/img/gallery/2.png" width="70" alt="" />
                          <div class="flex-1 align-items-center">
                            <h6 class="mb-0 fs--1 text-1000 fw-medium">The Busy Professional</h6>
                            <p class="fs--2 fw-normal text-info mb-0"></p>
                          </div>
                        </div>
                        <p class="card-text ps-7 ps-md-0 ps-xl-7 pt-md-4 pt-lg-3 pt-xl-0">"Balancing work and personal life can be challenging. This LMS platform has been a lifesaver. I can access courses and resources at my own pace, making it easy to fit learning into my busy schedule. The flexibility and convenience have significantly improved my professional development."</p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4 mb-3 mb-md-0">
                    <div class="card h-100 py-3">
                      <div class="card-body my-2">
                        <div class="d-flex align-items-center"><img class="img-fluid me-3 me-md-2 me-lg-3" src="/views/landing/assets/img/gallery/3.png" width="70" alt="" />
                          <div class="flex-1 align-items-center">
                            <h6 class="mb-0 fs--1 text-1000 fw-medium">The Career-Driven Individual</h6>
                            <p class="fs--2 fw-normal text-info mb-0"></p>
                          </div>
                        </div>
                        <p class="card-text ps-7 ps-md-0 ps-xl-7 pt-md-4 pt-lg-3 pt-xl-0">"I was looking for a platform that would help me advance my career. This LMS exceeded my expectations. The high-quality courses, industry-recognized certifications, and networking opportunities have given me the tools and connections to reach my professional goals."</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="carousel-item" data-bs-interval="2000">
                <div class="row h-100">
                  <div class="col-md-4 mb-3 mb-md-0">
                    <div class="card h-100 py-3">
                      <div class="card-body my-2">
                        <div class="d-flex align-items-center"><img class="img-fluid me-3 me-md-2 me-lg-3" src="/views/landing/assets/img/gallery/1.png" width="70" alt="" />
                          <div class="flex-1 align-items-center">
                            <h6 class="mb-0 fs--1 text-1000 fw-medium">The Student</h6>
                            <p class="fs--2 fw-normal text-info mb-0"></p>
                          </div>
                        </div>
                        <p class="card-text ps-7 ps-md-0 ps-xl-7 pt-md-4 pt-lg-3 pt-xl-0">"This LMS has revolutionized my learning experience. The interactive quizzes, video lectures, and discussion forums have made learning engaging and effective. The platform's accessibility and support have been invaluable to my academic success."</p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4 mb-3 mb-md-0">
                    <div class="card h-100 py-3">
                      <div class="card-body my-2">
                        <div class="d-flex align-items-center"><img class="img-fluid me-3 me-md-2 me-lg-3" src="/views/landing/assets/img/gallery/2.png" width="70" alt="" />
                          <div class="flex-1 align-items-center">
                            <h6 class="mb-0 fs--1 text-1000 fw-medium">The Lifelong Learner</h6>
                            <p class="fs--2 fw-normal text-info mb-0"></p>
                          </div>
                        </div>
                        <p class="card-text ps-7 ps-md-0 ps-xl-7 pt-md-4 pt-lg-3 pt-xl-0">"I've always been passionate about learning new things. This LMS has provided me with the perfect platform to explore my interests. The wide range of courses and the flexibility to learn at my own pace have made it easy to keep learning throughout my life."</p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4 mb-3 mb-md-0">
                    <div class="card h-100 py-3">
                      <div class="card-body my-2">
                        <div class="d-flex align-items-center"><img class="img-fluid me-3 me-md-2 me-lg-3" src="/views/landing/assets/img/gallery/3.png" width="70" alt="" />
                          <div class="flex-1 align-items-center">
                            <h6 class="mb-0 fs--1 text-1000 fw-medium">Remote Worker</h6>
                            <p class="fs--2 fw-normal text-info mb-0"></p>
                          </div>
                        </div>
                        <p class="card-text ps-7 ps-md-0 ps-xl-7 pt-md-4 pt-lg-3 pt-xl-0">"As a remote worker, it can be difficult to stay connected to my team. This LMS has helped bridge the gap. The collaborative features, such as group projects and discussion forums, have made it easy to work with my colleagues and stay up-to-date on the latest developments."</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="carousel-item">
                <div class="row h-100">
                  <div class="col-md-4 mb-3 mb-md-0">
                    <div class="card h-100 py-3">
                      <div class="card-body my-2">
                        <div class="d-flex align-items-center"><img class="img-fluid me-3 me-md-2 me-lg-3" src="/views/landing/assets/img/gallery/1.png" width="70" alt="" />
                          <div class="flex-1 align-items-center">
                            <h6 class="mb-0 fs--1 text-1000 fw-medium">The Small Business Owner</h6>
                            <p class="fs--2 fw-normal text-info mb-0"></p>
                          </div>
                        </div>
                        <p class="card-text ps-7 ps-md-0 ps-xl-7 pt-md-4 pt-lg-3 pt-xl-0">"I needed a cost-effective way to train my employees. This LMS has been a great solution. The easy-to-use authoring tools have allowed me to create custom training programs, and the platform's analytics have helped me track employee progress and identify areas for improvement.</p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4 mb-3 mb-md-0">
                    <div class="card h-100 py-3">
                      <div class="card-body my-2">
                        <div class="d-flex align-items-center"><img class="img-fluid me-3 me-md-2 me-lg-3" src="/views/landing/assets/img/gallery/2.png" width="70" alt="" />
                          <div class="flex-1 align-items-center">
                            <h6 class="mb-0 fs--1 text-1000 fw-medium">The International Student</h6>
                            <p class="fs--2 fw-normal text-info mb-0"></p>
                          </div>
                        </div>
                        <p class="card-text ps-7 ps-md-0 ps-xl-7 pt-md-4 pt-lg-3 pt-xl-0">"As an international student, adapting to a new education system can be challenging. This LMS has made the transition much smoother. The clear explanations, interactive exercises, and 24/7 support have been invaluable."</p>
                      </div>
                    </div>
                  </div>
                  <div class="col-md-4 mb-3 mb-md-0">
                    <div class="card h-100 py-3">
                      <div class="card-body my-2">
                        <div class="d-flex align-items-center"><img class="img-fluid me-3 me-md-2 me-lg-3" src="/views/landing/assets/img/gallery/3.png" width="70" alt="" />
                          <div class="flex-1 align-items-center">
                            <h6 class="mb-0 fs--1 text-1000 fw-medium">The Tech-Savvy Learner</h6>
                            <p class="fs--2 fw-normal text-info mb-0"></p>
                          </div>
                        </div>
                        <p class="card-text ps-7 ps-md-0 ps-xl-7 pt-md-4 pt-lg-3 pt-xl-0">"I appreciate the modern design and intuitive interface of this LMS. The platform's integration with other tools and technologies has made my learning experience even more efficient and enjoyable."</p>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            <div class="row px-3 px-md-0 mt-4">
              <div class="col-6 position-relative">
                <ol class="carousel-indicators">
                  <li class="active" data-bs-target="#carouselExampleDark" data-bs-slide-to="0"></li>
                  <li data-bs-target="#carouselExampleDark" data-bs-slide-to="1"></li>
                  <li data-bs-target="#carouselExampleDark" data-bs-slide-to="2"></li>
                </ol>
              </div>
              <div class="col-6 position-relative"><a class="carousel-control-prev carousel-icon z-index-2" href="#carouselExampleDark" role="button" data-bs-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">Previous</span></a><a class="carousel-control-next carousel-icon z-index-2" href="#carouselExampleDark" role="button" data-bs-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">Next</span></a></div>
            </div>
          </div>
        </div>
      </section>

      <section class="py-5 bg-light" id="privacy-policy">
        <div class="container">
            <div class="row">
                <div class="col-12">
                    <div class="card border-0 shadow">
                        <div class="card-header text-white text-center">
                            <h2 class="fw-bold">Privacy and Policies</h2>
                        </div>
                        <div class="card-body p-4">
                            <p class="lead">Your privacy is important to us. This privacy statement explains the personal data Knowbots processes, how Knowbots processes it, and for what purposes.</p>
                            <h4 class="mt-4">Information We Collect</h4>
                            <p>We collect information to provide better services to all our users. We collect information in the following ways:</p>
                            <ul>
                                <li><strong>Information you give us:</strong> For example, our service requires you to sign up for an Knowbots Account. When you do, we'll ask for personal information, like your name, email address, telephone number, or credit card.</li>
                                <li><strong>Information we get from your use of our services:</strong> We collect information about the services that you use and how you use them, like when you visit a website that uses our advertising services or view and interact with our ads and content.</li>
                            </ul>
                            <h4 class="mt-4">How We Use Information We Collect</h4>
                            <p>We use the information we collect from all our services to provide, maintain, protect, and improve them, to develop new ones, and to protect Knowbots and our users.</p>
                            <h4 class="mt-4">Information We Share</h4>
                            <p>We do not share personal information with companies, organizations, and individuals outside of Knowbots unless one of the following circumstances applies:</p>
                            <ul>
                                <li><strong>With your consent:</strong> We will share personal information with companies, organizations, or individuals outside of Knowbots when we have your consent to do so.</li>
                                <li><strong>For external processing:</strong> We provide personal information to our affiliates or other trusted businesses or persons to process it for us, based on our instructions and in compliance with our Privacy Policy and any other appropriate confidentiality and security measures.</li>
                                <li><strong>For legal reasons:</strong> We will share personal information with companies, organizations, or individuals outside of Knowbots if we have a good-faith belief that access, use, preservation, or disclosure of the information is reasonably necessary to meet any applicable law, regulation, legal process, or enforceable governmental request.</li>
                            </ul>
                            <h4 class="mt-4">Changes</h4>
                            <p>Our Privacy Policy may change from time to time. We will not reduce your rights under this Privacy Policy without your explicit consent. We will post any privacy policy changes on this page and, if the changes are significant, we will provide a more prominent notice (including, for certain services, email notification of privacy policy changes).</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>


      <!-- ============================================-->
      <!-- <section> begin ============================-->
      <!-- <section class="py-6 pb-0">

        <div class="container">
          <hr class="text-info opacity-25" />
          <div class="row py-7 justify-content-sm-between text-center text-md-start">
            <div class="col-md-6">
              <h1 class="fw-bold">Try for free!</h1>
              <p>Get limited 1 week free try our features!</p>
            </div>
            <div class="col-md-6 text-lg-end"><a class="btn btn-lg btn-danger rounded-pill me-4 me-md-3 me-lg-4" href="#" role="button">Learn more</a><a class="btn btn-light rounded-pill shadow fw-bold" href="#" role="button">Request Demo
                <svg class="bi bi-arrow-right" xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="#9C69E2" viewBox="0 0 16 16">
                  <path fill-rule="evenodd" d="M1 8a.5.5 0 0 1 .5-.5h11.793l-3.147-3.146a.5.5 0 0 1 .708-.708l4 4a.5.5 0 0 1 0 .708l-4 4a.5.5 0 0 1-.708-.708L13.293 8.5H1.5A.5.5 0 0 1 1 8z"></path>
                </svg></a></div>
          </div>
          <div class="row justify-content-lg-around"> -->
            <!-- <div class="col-12 col-sm-12 col-lg-3 mb-4 order-0 order-sm-0"><a class="text-decoration-none" href="#"><img class="img-fluid me-3" src="assets/img/icons/logo.png" alt="" /><span class="fw-bold fs-1 text-1000">DataWarehouse</span></a> -->
              <!-- <p class="mt-4">Warehouse Society, 234 <br />Bahagia Ave Street PRBW 29281</p>
              <p>info@warehouse.project<br />1-232-3434 (Main) </p>
            </div>
            <div class="col-6 col-sm-4 col-lg-3 mb-3 order-2 order-sm-1">
              <h6 class="lh-lg fw-bold mb-4">About</h6>
              <ul class="list-unstyled mb-md-4 mb-lg-0">
                <li class="lh-lg"><a class="text-dark fs--1 text-decoration-none" href="#!">Profile</a></li>
                <li class="lh-lg"><a class="text-dark fs--1 text-decoration-none" href="#!">Features</a></li>
                <li class="lh-lg"><a class="text-dark fs--1 text-decoration-none" href="#!">Careers</a></li>
                <li class="lh-lg"><a class="text-dark fs--1 text-decoration-none" href="#!">DW News</a></li>
              </ul>
            </div>
            <div class="col-6 col-sm-4 col-lg-3 mb-3 order-3 order-sm-2">
              <h6 class="lh-lg fw-bold mb-4"> Help </h6>
              <ul class="list-unstyled mb-md-4 mb-lg-0">
                <li class="lh-lg"><a class="text-dark fs--1 text-decoration-none" href="#!">Support</a></li>
                <li class="lh-lg"><a class="text-dark fs--1 text-decoration-none" href="#!">Sign Up </a></li>
                <li class="lh-lg"><a class="text-dark fs--1 text-decoration-none" href="#!">Guide</a></li>
                <li class="lh-lg"><a class="text-dark fs--1 text-decoration-none" href="#!">Reports</a></li>
                <li class="lh-lg"><a class="text-dark fs--1 text-decoration-none" href="#!">Q&amp;A</a></li>
              </ul>
            </div>
            <div class="col-12 col-sm-4 col-lg-3 mb-3 order-1 order-sm-3">
              <h6 class="lh-lg fw-bold mb-4">Social Media </h6>
              <ul class="list-unstyled mb-md-4 mb-lg-0">
                <li class="list-inline-item"><a class="text-dark fs--1 text-decoration-none" href="#!"><img class="img-fluid" src="assets/img/icons/f.png" width="40" alt="" /></a></li>
                <li class="list-inline-item"><a class="text-dark fs--1 text-decoration-none" href="#!"><img class="img-fluid" src="assets/img/icons/t.png" width="40" alt="" /></a></li>
                <li class="list-inline-item"><a class="text-dark fs--1 text-decoration-none" href="#!"><img class="img-fluid" src="assets/img/icons/i.png" width="40" alt="" /></a></li>
              </ul>
            </div>
          </div>
        </div> -->
        <!-- end of .container-->

      </section>
      <!-- <section> close ============================-->
      <!-- ============================================-->




    <!-- ============================================-->
    <!-- <section> begin ============================-->
    <section class="py-6">

      <div class="container">
        <div class="row flex-center px-3">
          <div class="col-12 col-md-6 px-md-0 order-1 order-md-0">
            <!-- <div class="text-center text-md-start">
              <p class="mb-0">This template is made with&nbsp;
                <svg class="bi bi-suit-heart-fill" xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="#9C69E2" viewBox="0 0 16 16">
                  <path d="M4 1c2.21 0 4 1.755 4 3.92C8 2.755 9.79 1 12 1s4 1.755 4 3.92c0 3.263-3.234 4.414-7.608 9.608a.513.513 0 0 1-.784 0C3.234 9.334 0 8.183 0 4.92 0 2.755 1.79 1 4 1z"></path>
                </svg>&nbsp;by&nbsp;<a class="text-1000" href="https://themewagon.com/" target="_blank">ThemeWagon </a>
              </p>
            </div>
          </div>
          <!-- <div class="col-12 col-md-6 text-center text-md-end mb-3 mb-md-0"> <a href="#"><img class="img-fluid" src="assets/img/icons/pre-footer.png" height="14" alt="" /></a></div> -->
        </div>
      </div>
      <!-- end of .container-->

    </section>
    <!-- <section> close ============================-->
    <!-- ============================================-->


  </main>
  <!-- ===============================================-->
  <!--    End of Main Content-->
  <!-- ===============================================-->




  <!-- ===============================================-->
  <!--    JavaScripts-->
  <!-- ===============================================-->
  <script src="/views/landing/vendors/@popperjs/popper.min.js"></script>
  <script src="/views/landing/vendors/bootstrap/bootstrap.min.js"></script>
  <script src="/views/landing/vendors/is/is.min.js"></script>
  <script src="https://polyfill.io/v3/polyfill.min.js?features=window.scroll"></script>
  <script src="/views/landing/assets/js/theme.js"></script>

  <link href="https://fonts.googleapis.com/css2?family=Lato:ital,wght@0,300;0,400;0,700;0,900;1,300;1,700;1,900&amp;display=swap" rel="stylesheet">

  <!-- Add these modal definitions after the main content but before the scripts -->
  <!-- Personalized Learning Modal -->
  <div class="modal fade" id="personalizedLearningModal" tabindex="-1" aria-labelledby="personalizedLearningModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="personalizedLearningModalLabel">Personalized Learning</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <h4>Adaptive Learning Path</h4>
          <p>Our system analyzes your learning style, pace, and preferences to create a customized learning journey just for you.</p>
          
          <h4>Smart Recommendations</h4>
          <p>Get personalized content recommendations based on your interests, goals, and previous learning activities.</p>
          
          <h4>Progress Tracking</h4>
          <p>Monitor your learning progress with detailed analytics and adjust your learning path accordingly.</p>
          
          <h4>Flexible Learning Schedule</h4>
          <p>Learn at your own pace with 24/7 access to course materials and flexible deadlines.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Interactive Content Modal -->
  <div class="modal fade" id="interactiveContentModal" tabindex="-1" aria-labelledby="interactiveContentModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="interactiveContentModalLabel">Interactive Content</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <h4>Multimedia Learning Materials</h4>
          <p>Access a rich variety of content formats including videos, animations, and interactive presentations.</p>
          
          <h4>Interactive Assessments</h4>
          <p>Engage with dynamic quizzes, simulations, and exercises that provide immediate feedback.</p>
          
          <h4>Gamified Learning</h4>
          <p>Stay motivated with gamification elements like points, badges, and leaderboards.</p>
          
          <h4>Hands-on Practice</h4>
          <p>Apply your knowledge through practical exercises and real-world scenarios.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Collaborative Learning Modal -->
  <div class="modal fade" id="collaborativeLearningModal" tabindex="-1" aria-labelledby="collaborativeLearningModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="collaborativeLearningModalLabel">Collaborative Learning</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <h4>Discussion Forums</h4>
          <p>Engage in meaningful discussions with peers and experts on various topics.</p>
          
          <h4>Group Projects</h4>
          <p>Work together on team assignments and learn from diverse perspectives.</p>
          
          <h4>Peer Review</h4>
          <p>Give and receive feedback to improve learning outcomes.</p>
          
          <h4>Expert Mentoring</h4>
          <p>Connect with industry experts and experienced mentors for guidance.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Certification Modal -->
  <div class="modal fade" id="certificationModal" tabindex="-1" aria-labelledby="certificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="certificationModalLabel">Certification and Badges</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <h4>Industry-Recognized Certificates</h4>
          <p>Earn verified certificates upon course completion to showcase your expertise.</p>
          
          <h4>Digital Badges</h4>
          <p>Collect and share digital badges for specific skills and achievements.</p>
          
          <h4>Professional Portfolio</h4>
          <p>Build a comprehensive portfolio of your learning achievements.</p>
          
          <h4>Social Sharing</h4>
          <p>Share your accomplishments on professional networks and social media.</p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Update the Learn More buttons to trigger modals -->
  <script>
  document.addEventListener('DOMContentLoaded', function() {
    // Update the learn more buttons to trigger respective modals
    const learnMoreButtons = document.querySelectorAll('.learn-more-btn');
    const modalIds = [
      '#personalizedLearningModal',
      '#interactiveContentModal', 
      '#collaborativeLearningModal',
      '#certificationModal'
    ];
    
    learnMoreButtons.forEach((button, index) => {
      if (index < modalIds.length) {
        button.setAttribute('data-bs-toggle', 'modal');
        button.setAttribute('data-bs-target', modalIds[index]);
      }
    });
  });
  </script>

  <!-- Add this script before closing body tag -->
  <script>
    // Mobile menu functionality
    const mobileMenuButton = document.querySelector('.mobile-menu-button');
    const mobileMenu = document.querySelector('.mobile-menu');
    const menuIcon = document.querySelector('.menu-icon');
    const closeIcon = document.querySelector('.close-icon');

    mobileMenuButton.addEventListener('click', () => {
      mobileMenu.classList.toggle('hidden');
      menuIcon.classList.toggle('hidden');
      closeIcon.classList.toggle('hidden');
    });

    // Navbar scroll behavior
    const navbar = document.querySelector('nav');
    let lastScroll = 0;

    window.addEventListener('scroll', () => {
      const currentScroll = window.pageYOffset;
      
      if (currentScroll <= 0) {
        navbar.classList.remove('shadow-lg');
        return;
      }
      
      if (currentScroll > lastScroll) {
        // Scrolling down
        navbar.classList.add('-translate-y-full', 'shadow-lg');
      } else {
        // Scrolling up
        navbar.classList.remove('-translate-y-full');
        navbar.classList.add('shadow-lg');
      }
      
      lastScroll = currentScroll;
    });
  </script>

  <!-- Privacy Policy Modal -->
  <div id="privacyPolicyModal" class="fixed inset-0 z-50 hidden overflow-y-auto">
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black bg-opacity-50 transition-opacity"></div>
    
    <!-- Modal Content -->
    <div class="relative min-h-screen flex items-center justify-center p-4">
        <div class="relative bg-white rounded-2xl max-w-4xl w-full max-h-[90vh] overflow-hidden shadow-xl">
            <!-- Modal Header -->
            <div class="bg-white px-6 py-4 border-b border-gray-200 sticky top-0 z-10">
                <div class="flex items-center justify-between">
                    <h3 class="text-xl font-semibold text-gray-900">Privacy Policy</h3>
                    <button onclick="closePrivacyModal()" class="text-gray-400 hover:text-gray-500 focus:outline-none">
                        <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
                <!-- Search Bar -->
                <div class="mt-4">
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
            </div>

            <!-- Modal Body -->
            <div class="px-6 py-4 overflow-y-auto max-h-[calc(90vh-140px)]" id="policyContent">
                <div class="space-y-6">
                    <section>
                        <h4 class="text-xl font-semibold text-gray-900">1. Information Collection and Use</h4>
                        <div class="mt-4 space-y-3 text-gray-600">
                            <p>We collect several types of information for various purposes to provide and improve our Service to you:</p>
                            <div class="ml-4 space-y-3">
                                <div>
                                    <h5 class="font-medium text-gray-900">1.1 Personal Data</h5>
                                    <ul class="list-disc ml-4 mt-2 space-y-1">
                                        <li>Name and contact details</li>
                                        <li>Educational institution information</li>
                                        <li>Academic records and progress</li>
                                        <li>Login credentials and account information</li>
                                    </ul>
                                </div>
                                <div>
                                    <h5 class="font-medium text-gray-900">1.2 Usage Data</h5>
                                    <ul class="list-disc ml-4 mt-2 space-y-1">
                                        <li>Course interaction and completion data</li>
                                        <li>Assessment results and feedback</li>
                                        <li>Learning preferences and patterns</li>
                                        <li>Technical session information</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </section>

                    <section class="mt-8">
                        <h4 class="text-xl font-semibold text-gray-900">2. Data Protection</h4>
                        <div class="mt-4 space-y-3 text-gray-600">
                            <p>We implement robust security measures to protect your personal information:</p>
                            <ul class="list-disc ml-4 space-y-2">
                                <li>End-to-end encryption for sensitive data</li>
                                <li>Regular security audits and updates</li>
                                <li>Strict access controls and authentication</li>
                                <li>Compliance with educational data protection standards</li>
                            </ul>
                        </div>
                    </section>

                    <section class="mt-8">
                        <h4 class="text-xl font-semibold text-gray-900">3. Data Usage and Sharing</h4>
                        <div class="mt-4 space-y-3 text-gray-600">
                            <p>Your information is used strictly for:</p>
                            <ul class="list-disc ml-4 space-y-2">
                                <li>Providing and improving educational services</li>
                                <li>Personalizing learning experiences</li>
                                <li>Academic progress tracking and reporting</li>
                                <li>Required institutional compliance</li>
                            </ul>
                        </div>
                    </section>

                    <section class="mt-8">
                        <h4 class="text-xl font-semibold text-gray-900">4. Your Rights</h4>
                        <div class="mt-4 space-y-3 text-gray-600">
                            <p>You have the right to:</p>
                            <ul class="list-disc ml-4 space-y-2">
                                <li>Access your personal data</li>
                                <li>Request data correction or deletion</li>
                                <li>Opt-out of certain data processing</li>
                                <li>Receive a copy of your data</li>
                            </ul>
                        </div>
                    </section>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="bg-gray-50 px-6 py-4 border-t border-gray-200">
                <button onclick="closePrivacyModal()" 
                        class="w-full sm:w-auto px-6 py-2.5 bg-gray-600 text-white rounded-lg hover:bg-gray-700 
                               transition-colors duration-200 text-sm font-medium focus:outline-none focus:ring-2 
                               focus:ring-gray-500 focus:ring-offset-2">
                    Close
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Add this script before closing body tag -->
<script>
    // Privacy Policy Modal Functions
    function openPrivacyModal() {
        document.getElementById('privacyPolicyModal').classList.remove('hidden');
        document.body.style.overflow = 'hidden';
    }

    function closePrivacyModal() {
        document.getElementById('privacyPolicyModal').classList.add('hidden');
        document.body.style.overflow = 'auto';
    }

    // Search Functionality
    document.getElementById('policySearch').addEventListener('input', function(e) {
        const searchText = e.target.value.toLowerCase();
        const content = document.getElementById('policyContent');
        const textNodes = [];
        
        // Get all text nodes
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
        
        // Remove existing highlights
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

<!-- Footer -->
<footer class="bg-white border-t border-gray-200">
  <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <!-- Company Info -->
      <div>
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Knowbots</h3>
        <p class="text-gray-600 text-sm">
          Empowering education through innovative learning solutions.
        </p>
      </div>
      
      <!-- Quick Links -->
      <div>
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Quick Links</h3>
        <ul class="space-y-2">
          <li>
            <a href="/privacy"
                class="group relative inline-flex items-center px-5 py-2.5 rounded-lg text-sm font-medium
                        border border-primary/30 text-gray-700
                        hover:bg-primary/5 hover:border-primary/50 hover:text-primary
                        transition-all duration-300 ease-in-out">
              <span class="flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                View Privacy Policies
              </span>
              <svg class="w-4 h-4 ml-2 transform transition-transform duration-300 group-hover:translate-x-1" 
                   fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                      d="M9 5l7 7-7 7" />
              </svg>
            </a>
          </li>
          <li>
            <a href="#" class="text-gray-600 hover:text-primary text-sm transition-colors duration-200 ml-1">
              Terms of Service
            </a>
          </li>
          <li>
            <a href="#" class="text-gray-600 hover:text-primary text-sm transition-colors duration-200 ml-1">
              Contact Us
            </a>
          </li>
        </ul>
      </div>
      
      <!-- Contact Info -->
      <div>
        <h3 class="text-lg font-semibold text-gray-900 mb-4">Contact</h3>
        <ul class="space-y-2">
          <li class="text-gray-600 text-sm">Email: support@knowbots.com</li>
          <li class="text-gray-600 text-sm">Phone: (555) 123-4567</li>
        </ul>
      </div>
    </div>
    
    <!-- Copyright -->
    <div class="mt-8 pt-8 border-t border-gray-200">
      <p class="text-center text-gray-600 text-sm">
        © 2024 Knowbots. All rights reserved.
      </p>
    </div>
  </div>
</footer>

</body>

</html>