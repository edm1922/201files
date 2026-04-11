<x-app-layout>
    @push('styles')
        {{-- Google Fonts: Poppins --}}
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
        
        {{-- Isolated CSS --}}
        <link rel="stylesheet" href="{{ asset('css/about.css') }}">
    @endpush

    <a href="{{ route('dashboard') }}" class="about-back-btn">
        <i class="fas fa-arrow-left me-2"></i> Back to System
    </a>

    <div class="about-wrapper">
        <div class="container py-5">
            
            {{-- ── Hero Section ── --}}
            <div class="text-center mb-5 animate-slide-down">
                <span class="subheading d-block mb-2 text-uppercase letter-spacing-2">Overview</span>
                <h1 class="display-3 fw-900 text-dark mb-4">About the Project</h1>
                <div class="mx-auto heading-underline mb-4"></div>
                <p class="lead text-muted mx-auto about-description">
                    A modern, secure electronic document management system designed to streamline archiving and tracking of critical company files.
                </p>
            </div>

            <div class="row justify-content-center g-5">
                
                <div class="col-lg-10">
                    <div class="about-section-card p-4 p-md-5 mb-5 animate-fade-in-up-scroll">
                        <div class="d-flex align-items-center mb-4">
                            <div class="ronaldo-icon me-3">
                                <i class="fas fa-rocket"></i>
                            </div>
                            <h2 class="h3 fw-bold mb-0">Project Purpose</h2>
                        </div>
                        <p class="text-secondary leading-loose">
                            The <strong>CSC Document Management System</strong> was developed to bridge the gap between physical and digital document archiving. 
                            It serves as a central hub for tracking 201 Files, Departmental Documents, and monitoring storage utilization with high precision, ensuring data integrity and rapid retrieval.
                        </p>
                    </div>

                    {{-- ── Middle: Development Team ── --}}
                    <div class="mb-5">
                        <div class="text-center mb-5 animate-fade-in-up-scroll">
                            <span class="subheading d-block mb-1 text-uppercase">The Creators</span>
                            <h2 class="h2 fw-bold text-dark">Developer Team</h2>
                        </div>

                        <div class="row g-4 justify-content-center">
                            
                            {{-- Developer 1: Ebszar --}}
                            <div class="col-md-6 animate-fade-in-up-scroll" style="transition-delay: 0.1s;">
                                <div class="dev-profile-box p-4 h-100">
                                    <div class="text-center mb-4">
                                        <div class="dev-avatar-ring mx-auto mb-3">
                                            <img src="{{ asset('image/P1.jpg') }}" alt="Ebszar A. Lapaz" class="dev-avatar">
                                        </div>
                                        <h3 class="h4 fw-bold mb-1">Ebszar A. Lapaz</h3>
                                        <!-- <span class="badge bg-soft-danger text-danger rounded-pill px-3">Lead Developer</span> -->
                                    </div>
                                    
                                    <div class="mt-4">
                                        <div class="info-row d-flex justify-content-between py-2 border-bottom">
                                            <span class="info-label text-muted small fw-600 text-uppercase">Course</span>
                                            <span class="info-value text-dark small text-end">BS Information Technology</span>
                                        </div>
                                        <div class="info-row d-flex justify-content-between py-2 border-bottom">
                                            <span class="info-label text-muted small fw-600 text-uppercase">School</span>
                                            <span class="info-value text-dark small text-end">Ramon Magsaysay Memorial Colleges</span>
                                        </div>
                                        <div class="info-row d-flex justify-content-between py-2 border-bottom">
                                            <span class="info-label text-muted small fw-600 text-uppercase">Email</span>
                                            <span class="info-value text-dark small text-break text-end">itsebs758@gmail.com</span>
                                        </div>
                                        <div class="info-row d-flex justify-content-between py-2">
                                            <span class="info-label text-muted small fw-600 text-uppercase">Phone</span>
                                            <span class="info-value text-dark small text-end">
                                                +63 935 495 6498
                                            </span>
                                        </div>
                                        <div class="info-row d-flex justify-content-between py-2">
                                            <span class="info-label text-muted small fw-600 text-uppercase">Github</span>
                                              <span class="info-value text-dark small text-end">
                                                <a href="https://github.com/Ebxxx" target="_blank" class="text-brand-red text-decoration-none">github.com/zeb</a>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Developer 2: Loyd --}}
                            <div class="col-md-6 animate-fade-in-up-scroll" style="transition-delay: 0.2s;">
                                <div class="dev-profile-box p-4 h-100">
                                    <div class="text-center mb-4">
                                        <div class="dev-avatar-ring mx-auto mb-3">
                                            <img src="{{ asset('image/p2.png') }}" alt="Loyd Oliver B. Pino" class="dev-avatar">
                                        </div>
                                        <h3 class="h4 fw-bold mb-1">Loyd Oliver B. Pino</h3>
                                        <!-- <span class="badge bg-soft-warning text-warning rounded-pill px-3">UI/UX Engineer</span> -->
                                    </div>
                                    
                                    <div class="mt-4">
                                        <div class="info-row d-flex justify-content-between py-2 border-bottom">
                                            <span class="info-label text-muted small fw-600 text-uppercase">Course</span>
                                            <span class="info-value text-dark small text-end">BS Information Technology</span>
                                        </div>
                                        <div class="info-row d-flex justify-content-between py-2 border-bottom">
                                            <span class="info-label text-muted small fw-600 text-uppercase">School</span>
                                            <span class="info-value text-dark small text-end">Ramon Magsaysay Memorial Colleges</span>
                                        </div>
                                        <div class="info-row d-flex justify-content-between py-2 border-bottom">
                                            <span class="info-label text-muted small fw-600 text-uppercase">Email</span>
                                            <span class="info-value text-dark small text-break text-end">loydoliverpino@gmail.com</span>
                                        </div>
                                        <div class="info-row d-flex justify-content-between py-2">
                                            <span class="info-label text-muted small fw-600 text-uppercase">Phone</span>
                                            <span class="info-value text-dark small text-end">
                                                +63 963 834 0533
                                            </span>
                                        </div>
                                        <div class="info-row d-flex justify-content-between py-2">
                                            <span class="info-label text-muted small fw-600 text-uppercase">Github</span>
                                            <span class="info-value text-dark small text-end">
                                                <a href="https://github.com/Doliver510" target="_blank" class="text-brand-red text-decoration-none">github.com/Doliver510</a>
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>

                    {{-- ── Acknowledgements ── --}}
                    <div class="mb-5 animate-fade-in-up-scroll">
                        <div class="text-center mb-5">
                            <span class="subheading d-block mb-1 text-uppercase">Gratitude</span>
                            <h2 class="h2 fw-bold text-dark mb-3">Acknowledgements</h2>
                            <p class="text-secondary mx-auto" style="max-width: 600px;">
                                We would like to thanks our mentors and colleagues for their guidance, support, and contribution to the success of this project.
                            </p>
                        </div>

                        <div class="row g-4">
                            {{-- Mentors/Supervisors --}}
                            <div class="col-md-6">
                                <div class="acknowledgement-card p-4 h-100">
                                    <h4 class="h6 fw-bold text-muted text-uppercase mb-3 letter-spacing-1 border-bottom pb-2">Our Mentors</h4>
                                    <ul class="list-unstyled mb-0">
                                        <li class="mb-4 d-flex align-items-center">
                                            <div class="mentor-avatar-sm me-4 bg-soft-danger text-danger d-flex align-items-center justify-content-center">
                                                <img src="{{ asset('image/JemarPic.png') }}" alt="Jemar L. Barrera" class="w-100 h-100 object-fit-cover">
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark text-uppercase mb-1">Jemar L. Barrera</div>
                                                <div class="text-brand-red fw-600">Supervisor</div>
                                            </div>
                                        </li>
                                        <li class="d-flex align-items-center">
                                            <div class="mentor-avatar-sm me-4 bg-soft-danger text-danger d-flex align-items-center justify-content-center">
                                               <img src="{{ asset('image/CristinePic.png') }}" alt="Cristine Marie M. Bernales" class="w-100 h-100 object-fit-cover">
                                            </div>
                                            <div>
                                                <div class="fw-bold text-dark text-uppercase mb-1">Cristine Marie M. Bernales</div>
                                                <div class="text-brand-red fw-600">HR recruitment officer</div>
                                            </div>
                                        </li>
                                    </ul>
                                </div>
                            </div>

                            {{-- Other Contributors (Fellow OJTs) --}}
                            <div class="col-md-6">
                                <div class="acknowledgement-card p-4 h-100">
                                    <h4 class="h6 fw-bold text-muted text-uppercase mb-3 letter-spacing-1 border-bottom pb-2">Support Team (OJT)</h4>
                                    <p class="small text-muted mb-3 opacity-75">Special thanks to our fellow OJTs who helped us navigate technical challenges:</p>
                                    <div class="d-flex flex-wrap gap-2">
                                        <span class="ojt-badge px-3 py-1 rounded-pill small">Lossaly Sobretodo</span>
                                        <span class="ojt-badge px-3 py-1 rounded-pill small">Maryam U. Musa</span>
                                         <span class="ojt-badge px-3 py-1 rounded-pill small">Lucy Zina</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- ── Bottom: Powered By ── --}}
                    <div class="text-center mt-5 pt-4 animate-fade-in-up-scroll">
                        <h4 class="h6 text-muted text-uppercase mb-4 letter-spacing-1">Powered By</h4>
                        <div class="d-flex flex-wrap justify-content-center gap-3">
                            <span class="tech-pill"><i class="fab fa-laravel text-danger me-2"></i> Laravel</span>
                            <span class="tech-pill"><i class="fab fa-php text-primary me-2"></i> PHP 8</span>
                            <span class="tech-pill"><i class="fas fa-database text-info me-2"></i> MySQL</span>
                            <span class="tech-pill"><i class="fab fa-js text-warning me-2"></i> Alpine.js</span>
                            <span class="tech-pill"><i class="fab fa-bootstrap text-purple me-2"></i> BS 5</span>
                            <span class="tech-pill"><i class="fas fa-search text-success me-2"></i> Meilisearch</span>
                        </div>
                        
                        <div class="mt-5 footer-note text-muted py-4">
                            <p class="mb-1">Developed as part of the <strong>OJT Internship Program</strong></p>
                            <p class="small">&copy; {{ date('Y') }} CSC Document Management System. All rights reserved.</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script src="{{ asset('js/about.js') }}"></script>
    @endpush
</x-app-layout>
