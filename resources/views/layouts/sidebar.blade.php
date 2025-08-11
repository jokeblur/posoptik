              <aside class="main-sidebar skin-red-light">
                  <!-- sidebar: style can be found in sidebar.less -->
                  <section class="sidebar">
                      <!-- Professional Header with Logo and Brand Name -->
                      <div class="sidebar-header" style="padding: 20px; border-bottom: 1px solid rgba(255,255,255,0.2); background-color: #a4193d; margin-top: 0; text-align: center;">
                          <!-- Logo and Brand Container -->
                          <div class="brand-container" style="margin-bottom: 15px;">
                              <!-- Logo Icon -->
                              <div class="logo-icon" style="width: 60px; height: 60px; background: rgba(255,255,255,0.15); border-radius: 50%; margin: 0 auto 12px; display: flex; align-items: center; justify-content: center; border: 2px solid rgba(255,255,255,0.3); overflow: hidden;">
                                  <img src="{{ asset('image/optik-melati.png') }}" alt="Optik Melati Logo" style="width: 45px; height: 45px; object-fit: contain; border-radius: 50%;">
                              </div>
                              <!-- Brand Name -->
                              <h1 class="brand-name" style="color: #ffffff; font-size: 18px; font-weight: 700; margin: 0; text-shadow: 0 2px 4px rgba(0,0,0,0.2); letter-spacing: 1px;">
                                  OPTIK MELATI
                              </h1>
                              <!-- Tagline -->
                              <p class="brand-tagline" style="color: rgba(255,255,255,0.8); font-size: 11px; margin: 5px 0 0 0; font-weight: 400; letter-spacing: 0.5px;">
                                  Pos System
                              </p>
                          </div>
                          
                          <!-- Hamburger Menu Button -->
                          <div class="hamburger-container" style="margin-top: 15px;">
                              <a href="#" class="sidebar-hamburger-toggle" data-toggle="push-menu" role="button" style="color: #fff; font-size: 14px; text-decoration: none; background: rgba(255,255,255,0.1); padding: 8px 16px; border-radius: 20px; border: 1px solid rgba(255,255,255,0.2); transition: all 0.3s ease; display: inline-block;">
                                  <i class="fa fa-bars" style="margin-right: 6px;"></i> Menu
                              </a>
                          </div>
                      </div>

                      <!-- sidebar menu: : style can be found in sidebar.less -->
                      <ul class="sidebar-menu" data-widget="tree" style="margin-top: 0; padding-top: 0;">
                          <li class="header" style="padding: 8px 15px; margin: 0; background-color: #a4193d; color: #fff; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">NAVIGASI</li>
                          <li style="margin: 0;"><a href="{{ route('dashboard') }}" style="padding: 8px 15px;"><i class="fa fa-dashboard"></i> <span>Dashboard</span></a></li>
                          
                          @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                          <li class="header" style="padding: 8px 15px; margin: 8px 0 0 0; background-color: #a4193d; color: #fff; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">ADMINISTRASI</li>
                          <li style="margin: 0;"><a href="{{ route('openclose.day') }}" style="padding: 8px 15px;"><i class="fa fa-calendar-check-o"></i> <span>Open/Close Day</span></a></li>
                          @endif
                          
                          @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                          <li class="header" style="padding: 8px 15px; margin: 8px 0 0 0; background-color: #a4193d; color: #fff; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">MASTER DATA</li>
                          <li style="margin: 0;"><a href="{{ route('branch.index') }}" style="padding: 8px 15px;"><i class="fa fa-building"></i> <span>Data Cabang</span></a></li>
                          <li style="margin: 0;"><a href="{{ route('dokter.index') }}" style="padding: 8px 15px;"><i class="fa fa-user-md"></i> <span>Data Dokter</span></a></li>
                          <li style="margin: 0;"><a href="{{ route('user.index') }}" style="padding: 8px 15px;"><i class="fa fa-user"></i> <span>Data User</span></a></li>
                          @endif
                          
                          @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                          <li class="header" style="padding: 8px 15px; margin: 8px 0 0 0; background-color: #a4193d; color: #fff; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">INVENTORY</li>
                          <li style="margin: 0;"><a href="{{ route('frame.index') }}" style="padding: 8px 15px;"><i class="fa fa-eye"></i> <span>Data Frame</span></a></li>
                          <li style="margin: 0;"><a href="{{ route('lensa.index') }}" style="padding: 8px 15px;"><i class="fa fa-circle-o"></i> <span>Data Lensa</span></a></li>
                          <li style="margin: 0;"><a href="{{ route('aksesoris.index') }}" style="padding: 8px 15px;"><i class="fa fa-gift"></i> <span>Data Aksesoris</span></a></li>
                          <li style="margin: 0;"><a href="{{ route('kategori.index') }}" style="padding: 8px 15px;"><i class="fa fa-tags"></i> <span>Data Kategori</span></a></li>
                          @endif
                          
                          <!-- Stock Transfer Menu - accessible by all authenticated users -->
                          <li class="header" style="padding: 8px 15px; margin: 8px 0 0 0; background-color: #a4193d; color: #fff; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">TRANSFER STOK</li>
                          <li style="margin: 0;"><a href="{{ route('stock-transfer.dashboard') }}" style="padding: 8px 15px;"><i class="fa fa-dashboard"></i> <span>Dashboard Transfer Stok</span></a></li>
                        <li style="margin: 0;"><a href="{{ route('stock-transfer.index') }}" style="padding: 8px 15px;"><i class="fa fa-exchange"></i> <span>Transfer Stok Antar Cabang</span></a></li>
                          
                          <!-- Menu Transaksi untuk semua role yang relevan -->
                          <li class="header" style="padding: 8px 15px; margin: 8px 0 0 0; background-color: #a4193d; color: #fff; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">TRANSAKSI</li>
                          @if(auth()->user()->isKasir() || auth()->user()->isAdmin() || auth()->user()->isSuperAdmin())
                          <li style="margin: 0;"><a href="{{ route('pasien.index') }}" style="padding: 8px 15px;"><i class="fa fa-user-plus"></i> <span>Data Pasien</span></a></li>
                          <li style="margin: 0;"><a href="{{ route('penjualan.index') }}" style="padding: 8px 15px;"><i class="fa fa-upload"></i> <span>Data Penjualan</span></a></li>
                          <li style="margin: 0;"><a href="{{ route('barcode.scan') }}" style="padding: 8px 15px;"><i class="fa fa-qrcode"></i> <span>Scan Barcode</span></a></li>
                          @endif
                          
                          @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                          <li class="header" style="padding: 8px 15px; margin: 8px 0 0 0; background-color: #a4193d; color: #fff; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">LAPORAN</li>
                          <li style="margin: 0;"><a href="{{ route('laporan.pos') }}" style="padding: 8px 15px;"><i class="fa fa-file-text"></i> <span>Laporan POS</span></a></li>
                          <li style="margin: 0;"><a href="{{ route('laporan.bpjs') }}" style="padding: 8px 15px;"><i class="fa fa-file-text"></i> <span>Laporan BPJS</span></a></li>
                          @endif
                          
                          @if(auth()->user()->isSuperAdmin() || auth()->user()->isAdmin())
                          <li class="header" style="padding: 8px 15px; margin: 8px 0 0 0; background-color: #a4193d; color: #fff; font-size: 12px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;">SETTINGS</li>
                          <li style="margin: 0;"><a href="{{ route('sales.index') }}" style="padding: 8px 15px;"><i class="fa fa-user"></i> <span>Data Sales</span></a></li>
                          <li style="margin: 0;"><a href="{{ route('barcode.index') }}" style="padding: 8px 15px;"><i class="fa fa-barcode"></i> <span>Barcode</span></a></li>
                          @endif
                      </ul>
                  </section>
                  <!-- /.sidebar -->
              </aside>
              
              <!-- Mobile Hamburger Button (Fixed Position) -->
              <div class="mobile-hamburger-fixed" style="position: fixed !important; top: 20px !important; left: 20px !important; z-index: 99999 !important; display: block !important; width: 50px !important; height: 50px !important;">
                  <button class="mobile-hamburger-btn" onclick="toggleMobileSidebar()" style="background: #a4193d !important; color: white !important; border: none !important; padding: 0 !important; border-radius: 8px !important; box-shadow: 0 4px 12px rgba(0,0,0,0.15) !important; cursor: pointer !important; transition: all 0.3s ease !important; width: 100% !important; height: 100% !important; display: flex !important; align-items: center !important; justify-content: center !important;">
                      <i class="fa fa-bars" style="font-size: 18px !important; color: white !important;"></i>
                  </button>
              </div>
              
              <!-- Mobile Overlay -->
              <div class="sidebar-overlay" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.5); z-index: 999; display: none; opacity: 0; transition: opacity 0.3s ease;"></div>
              
              <script>
              // Global function untuk mobile sidebar toggle
              function toggleMobileSidebar() {
                  console.log('toggleMobileSidebar called');
                  
                  const sidebar = document.querySelector('.main-sidebar');
                  const overlay = document.querySelector('.sidebar-overlay');
                  
                  console.log('Sidebar element:', sidebar);
                  console.log('Overlay element:', overlay);
                  
                  if (!sidebar || !overlay) {
                      console.error('Sidebar or overlay not found!');
                      return;
                  }
                  
                  const isOpen = sidebar.classList.contains('mobile-active');
                  console.log('Is sidebar open:', isOpen);
                  
                  if (isOpen) {
                      // Close sidebar
                      sidebar.classList.remove('mobile-active');
                      sidebar.style.left = '-250px';
                      overlay.classList.remove('active');
                      overlay.style.display = 'none';
                      document.body.classList.remove('sidebar-open');
                      console.log('Sidebar closed');
                  } else {
                      // Open sidebar
                      sidebar.classList.add('mobile-active');
                      sidebar.style.left = '0px';
                      overlay.classList.add('active');
                      overlay.style.display = 'block';
                      overlay.style.opacity = '1';
                      document.body.classList.add('sidebar-open');
                      console.log('Sidebar opened');
                  }
              }
              
              // jQuery fallback dan event handlers
              $(document).ready(function() {
                  console.log('Sidebar script loaded');
                  
                  // Force sidebar setup for mobile
                  function setupMobileSidebar() {
                      if (window.innerWidth <= 768) {
                          console.log('Setting up mobile sidebar');
                          
                          // Show hamburger
                          $('.mobile-hamburger-fixed').css({
                              'display': 'block',
                              'visibility': 'visible'
                          });
                          
                          // Position sidebar
                          $('.main-sidebar').css({
                              'position': 'fixed',
                              'left': '-250px',
                              'width': '250px',
                              'height': '100vh',
                              'z-index': '1000',
                              'top': '0'
                          });
                      } else {
                          $('.mobile-hamburger-fixed').hide();
                      }
                  }
                  
                  setupMobileSidebar();
                  $(window).on('resize', setupMobileSidebar);
                  
                  // Overlay click to close
                  $(document).on('click', '.sidebar-overlay', function() {
                      console.log('Overlay clicked');
                      toggleMobileSidebar();
                  });
                  
                  // Menu item click to close
                  $(document).on('click', '.sidebar-menu a', function() {
                      if (window.innerWidth <= 768 && $('.main-sidebar').hasClass('mobile-active')) {
                          console.log('Menu clicked, closing sidebar');
                          toggleMobileSidebar();
                      }
                  });
                  
                  // Desktop hamburger
                  $(document).on('click', '.sidebar-hamburger-toggle', function(e) {
                      e.preventDefault();
                      if (window.innerWidth > 768) {
                          $('body').toggleClass('sidebar-collapse');
                      }
                  });
              });
              </script>