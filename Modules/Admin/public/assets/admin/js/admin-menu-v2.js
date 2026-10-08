// Reference-matched desktop collapse and mobile drawer behavior.
(function ($) {
    'use strict';

    // Legacy theme scripts call these helpers even when the old sidemenu
    // controller is not loaded. The v2 menu owns responsive layout changes.
    window.checkHoriMenu = window.checkHoriMenu || function () {};
    window.responsive = window.responsive || function () {};

    $(function () {
        const mobileBreakpoint = 992;
        const $body = $('body');
        const $sidebar = $('#adminSidebar');
        const $desktopToggle = $('#adminDesktopToggle');
        const $mobileToggle = $('#adminMobileToggle');
        const $overlay = $('#adminSidebarOverlay');
        const $desktopIcon = $desktopToggle.find('i');
        const $mobileIcon = $mobileToggle.find('i');

        if (!$sidebar.length) {
            return;
        }

        $body.addClass('admin-menu-v2');

        function isMobile() {
            return window.innerWidth < mobileBreakpoint;
        }

        function closeSubmenus() {
            $sidebar.find('.submenu.show').each(function () {
                if (window.bootstrap && bootstrap.Collapse) {
                    bootstrap.Collapse.getOrCreateInstance(this, { toggle: false }).hide();
                } else {
                    $(this).removeClass('show');
                }
            });
        }

        function setDesktopCollapsed(collapsed) {
            $sidebar.toggleClass('collapsed', collapsed);
            $body.toggleClass('sidebar-collapsed', collapsed);
            $desktopToggle.attr({
                'aria-expanded': collapsed ? 'false' : 'true',
                'aria-label': collapsed ? 'باز کردن منو' : 'جمع کردن منو'
            });
            $desktopIcon.toggleClass('fa-bars', !collapsed);
            $desktopIcon.toggleClass('fa-bars-staggered', collapsed);

            if (collapsed) {
                closeSubmenus();
            }
        }

        function openMobileMenu() {
            $sidebar.addClass('mobile-open');
            $overlay.addClass('show');
            $mobileToggle.addClass('active').attr({
                'aria-expanded': 'true',
                'aria-label': 'بستن منو'
            });
            $mobileIcon.removeClass('fa-bars').addClass('fa-xmark');
            $body.addClass('admin-menu-mobile-open').css('overflow', 'hidden');
        }

        function closeMobileMenu() {
            $sidebar.removeClass('mobile-open');
            $overlay.removeClass('show');
            $mobileToggle.removeClass('active').attr({
                'aria-expanded': 'false',
                'aria-label': 'نمایش منو'
            });
            $mobileIcon.removeClass('fa-xmark').addClass('fa-bars');
            $body.removeClass('admin-menu-mobile-open').css('overflow', '');
        }

        function activateCurrentLink() {
            const normalizeUrl = function (url) {
                const normalizedUrl = new URL(url, window.location.origin);
                // Query values such as formId identify a view within the same
                // menu page and should not prevent its menu item becoming active.
                normalizedUrl.search = '';
                normalizedUrl.hash = '';

                return normalizedUrl.href.replace(/\/$/, '');
            };

            const currentUrl = normalizeUrl(window.location.href);
            let $activeLink = $();

            $sidebar.find('a.admin-menu-link[href]').each(function () {
                const rawHref = this.getAttribute('href');

                if (!rawHref || rawHref === '#' || rawHref.charAt(0) === '#') {
                    return;
                }

                const linkUrl = normalizeUrl(this.href);
                if (linkUrl === currentUrl) {
                    $activeLink = $(this);
                }
            });

            if (!$activeLink.length) {
                return;
            }

            $sidebar.find('.admin-menu-link').removeClass('active');
            $activeLink.addClass('active');
            $activeLink.parents('.submenu').each(function () {
                $(this).addClass('show');
                $(this).prev('.admin-menu-link')
                    .addClass('is-open')
                    .attr('aria-expanded', 'true');
            });

            window.requestAnimationFrame(function () {
                const sidebar = $sidebar[0];
                const link = $activeLink[0];
                const sidebarRect = sidebar.getBoundingClientRect();
                const linkRect = link.getBoundingClientRect();
                const padding = 12;

                if (linkRect.top < sidebarRect.top + padding) {
                    sidebar.scrollTo({
                        top: sidebar.scrollTop + linkRect.top - sidebarRect.top - padding,
                        behavior: 'smooth'
                    });
                } else if (linkRect.bottom > sidebarRect.bottom - padding) {
                    sidebar.scrollTo({
                        top: sidebar.scrollTop + linkRect.bottom - sidebarRect.bottom + padding,
                        behavior: 'smooth'
                    });
                }
            });
        }

        function handleResize() {
            if (isMobile()) {
                setDesktopCollapsed(false);
                closeMobileMenu();
            } else {
                closeMobileMenu();
            }
        }

        $desktopToggle.on('click', function () {
            setDesktopCollapsed(!$sidebar.hasClass('collapsed'));
        });

        $mobileToggle.on('click', function (event) {
            event.preventDefault();
            $sidebar.hasClass('mobile-open') ? closeMobileMenu() : openMobileMenu();
        });

        $overlay.on('click', closeMobileMenu);

        $sidebar.on('click', '.admin-menu-link[data-bs-toggle="collapse"]', function (event) {
            const trigger = this;
            const submenuId = trigger.getAttribute('aria-controls');
            const submenu = submenuId ? document.getElementById(submenuId) : null;
            const wasCollapsed = !isMobile() && $sidebar.hasClass('collapsed');

            if (!wasCollapsed || !submenu) {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();
            setDesktopCollapsed(false);
            $(trigger).removeClass('active').addClass('is-open').attr('aria-expanded', 'true');

            window.requestAnimationFrame(function () {
                if (window.bootstrap && bootstrap.Collapse) {
                    bootstrap.Collapse.getOrCreateInstance(submenu, { toggle: false }).show();
                } else {
                    $(submenu).addClass('show');
                }
            });
        });

        $sidebar.on('click', '.admin-menu-link:not([data-bs-toggle="collapse"])', function () {
            if (isMobile()) {
                closeMobileMenu();
            }
        });

        $sidebar.on('show.bs.collapse', '.submenu', function () {
            $(this).prev('.admin-menu-link')
                .removeClass('active')
                .addClass('is-open')
                .attr('aria-expanded', 'true');
        });

        $sidebar.on('hide.bs.collapse', '.submenu', function () {
            $(this).prev('.admin-menu-link')
                .removeClass('active')
                .removeClass('is-open')
                .attr('aria-expanded', 'false');
        });

        $(document).on('keydown', function (event) {
            if (event.key === 'Escape' && $sidebar.hasClass('mobile-open')) {
                closeMobileMenu();
                $mobileToggle.trigger('focus');
            }
        });

        let previousMobileState = isMobile();
        $(window).on('resize.adminMenuV2', function () {
            const currentMobileState = isMobile();

            if (currentMobileState !== previousMobileState) {
                handleResize();
                previousMobileState = currentMobileState;
            }
        });

        activateCurrentLink();
        handleResize();
    });
})(jQuery);
