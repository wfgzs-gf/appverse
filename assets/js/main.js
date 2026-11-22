/**
 * Appverse front-end behaviour.
 *
 * Vanilla ES5-ish IIFE. No jQuery, no dependencies, no build step.
 * Progressive enhancement only: the app is usable before this file runs.
 *
 * Handles:
 *   - hero carousel (autoplay, dots, swipe, pause on hover/hidden, reduced motion)
 *   - local tabs for the single-app screen
 *   - AJAX reactions (like / dislike) with optimistic UI
 *   - download modal + download counter (appverse_download)
 *   - daily check-in (appverse_checkin)
 *   - toast queue, modal focus trap, body scroll lock, data-toast buttons
 *
 * Global config comes from window.AppverseData (wp_localize_script).
 */
(function (window, document) {
	'use strict';

	var cfg = window.AppverseData || {};
	var i18n = cfg.i18n || {};
	var reduceMotion = window.matchMedia ? window.matchMedia('(prefers-reduced-motion: reduce)') : { matches: false };

	/* ---------------------------------------------------------------------
	 * Tiny helpers
	 * ------------------------------------------------------------------ */

	function $(selector, context) {
		return (context || document).querySelector(selector);
	}

	function $$(selector, context) {
		return Array.prototype.slice.call((context || document).querySelectorAll(selector));
	}

	function closest(el, selector) {
		if (!el || el.nodeType !== 1) {
			return null;
		}
		if (el.closest) {
			return el.closest(selector);
		}
		while (el && el !== document) {
			if (el.matches && el.matches(selector)) {
				return el;
			}
			el = el.parentElement;
		}
		return null;
	}

	function setText(el, value) {
		if (el) {
			el.textContent = value;
		}
	}

	function parseCount(value) {
		var n = parseInt(String(value == null ? '' : value).replace(/[^\d]/g, ''), 10);
		return isNaN(n) ? 0 : n;
	}

	function formatCount(value) {
		var n = Math.max(0, parseInt(value, 10) || 0);
		if (n >= 100000000) {
			return (Math.round((n / 100000000) * 10) / 10) + '亿';
		}
		if (n >= 10000) {
			return (Math.round((n / 10000) * 10) / 10) + '万';
		}
		return String(n);
	}

	function isLoggedIn() {
		return !!cfg.isLoggedIn;
	}

	/* ---------------------------------------------------------------------
	 * Toast
	 * ------------------------------------------------------------------ */

	var toastEl = null;
	var toastTimer = null;

	function toast(message, type) {
		if (!message) {
			return;
		}
		if (!toastEl) {
			toastEl = $('.av-toast[data-toast]');
		}
		if (!toastEl) {
			return;
		}
		toastEl.textContent = String(message);
		toastEl.className = 'av-toast is-visible' + (type ? ' av-toast--' + type : '');
		window.clearTimeout(toastTimer);
		toastTimer = window.setTimeout(function () {
			toastEl.classList.remove('is-visible');
			toastEl.className = 'av-toast';
		}, 2600);
	}

	/* ---------------------------------------------------------------------
	 * AJAX
	 * ------------------------------------------------------------------ */

	function ajax(action, payload) {
		var body = new URLSearchParams();
		body.append('action', action);
		body.append('nonce', cfg.nonce || '');
		Object.keys(payload || {}).forEach(function (key) {
			if (payload[key] != null) {
				body.append(key, payload[key]);
			}
		});

		return fetch(cfg.ajaxUrl, {
			method: 'POST',
			credentials: 'same-origin',
			headers: { 'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8' },
			body: body.toString()
		}).then(function (response) {
			return response.json().catch(function () {
				return { success: false, data: { message: i18n.error || '操作失败，请稍后重试' } };
			}).then(function (json) {
				return { ok: response.ok, json: json || {} };
			});
		});
	}

	function dataOf(result) {
		return (result && result.json && result.json.data) || {};
	}

	function messageOf(result, fallback) {
		var data = dataOf(result);
		return data.message || fallback || i18n.error || '操作失败，请稍后重试';
	}

	function handleAuthError(result) {
		var data = dataOf(result);
		var loginUrl = data.loginUrl || cfg.loginUrl;
		toast(data.message || i18n.login || '登录后才能操作');
		if (loginUrl) {
			window.setTimeout(function () {
				window.location.href = loginUrl;
			}, 1100);
		}
		return true;
	}

	function requireLogin() {
		if (isLoggedIn()) {
			return true;
		}
		toast(i18n.login || '登录后才能操作');
		if (cfg.loginUrl) {
			window.setTimeout(function () {
				window.location.href = cfg.loginUrl;
			}, 1100);
		}
		return false;
	}

	/* ---------------------------------------------------------------------
	 * Hero carousel
	 * ------------------------------------------------------------------ */

	function initCarousel(root) {
		var viewport = $('.av-hero__viewport', root);
		var slides = $$('.av-hero__slide', root);
		var dots = $$('.av-hero__dot', root);

		if (!viewport || slides.length < 2) {
			return;
		}

		var index = Math.max(0, slides.findIndex(function (slide) {
			return slide.classList.contains('is-active');
		}));
		var timer = null;
		var hovering = false;
		var userPaused = false;
		var frame = 0;
		var drag = null;
		var suppressClick = false;
		var initialised = false;

		function slideCenter(i) {
			var rect = slides[i].getBoundingClientRect();
			var box = viewport.getBoundingClientRect();
			return viewport.scrollLeft + (rect.left - box.left) + rect.width / 2;
		}

		function scrollToSlide(i, smooth) {
			var slide = slides[i];
			if (!slide) {
				return;
			}
			var rect = slide.getBoundingClientRect();
			var box = viewport.getBoundingClientRect();
			var left = viewport.scrollLeft + (rect.left - box.left) - (viewport.clientWidth - rect.width) / 2;

			if (typeof viewport.scrollTo === 'function') {
				viewport.scrollTo({
					left: left,
					behavior: smooth && !reduceMotion.matches ? 'smooth' : 'auto'
				});
			} else {
				viewport.scrollLeft = left;
			}
		}

		function paint(i) {
			slides.forEach(function (slide, n) {
				slide.classList.toggle('is-active', n === i);
			});
			dots.forEach(function (dot, n) {
				var active = n === i;
				dot.classList.toggle('is-active', active);
				if (dot.setAttribute) {
					dot.setAttribute('aria-selected', active ? 'true' : 'false');
					dot.setAttribute('tabindex', active ? '0' : '-1');
				}
			});
		}

		function setIndex(i) {
			index = ((i % slides.length) + slides.length) % slides.length;
			paint(index);
		}

		function goTo(i, smooth) {
			setIndex(i);
			scrollToSlide(index, smooth !== false);
			restart();
		}

		function nearestIndex() {
			var center = viewport.scrollLeft + viewport.clientWidth / 2;
			var best = 0;
			var distance = Infinity;
			slides.forEach(function (slide, n) {
				var d = Math.abs(slideCenter(n) - center);
				if (d < distance) {
					distance = d;
					best = n;
				}
			});
			return best;
		}

		function syncFromScroll() {
			if (!initialised) {
				return;
			}
			setIndex(nearestIndex());
		}

		function onScroll() {
			if (frame) {
				return;
			}
			frame = window.requestAnimationFrame(function () {
				frame = 0;
				syncFromScroll();
			});
		}

		function stop() {
			window.clearInterval(timer);
			timer = null;
		}

		function start() {
			stop();
			if (reduceMotion.matches || userPaused || hovering || document.hidden) {
				return;
			}
			timer = window.setInterval(function () {
				if (document.hidden || hovering) {
					return;
				}
				goTo(index + 1, true);
			}, 5000);
		}

		function restart() {
			start();
		}

		// Initial state.
		paint(index);
		if (window.requestAnimationFrame) {
			window.requestAnimationFrame(function () {
				scrollToSlide(index, false);
				initialised = true;
			});
		} else {
			scrollToSlide(index, false);
			initialised = true;
		}
		start();

		viewport.addEventListener('scroll', onScroll, { passive: true });

		dots.forEach(function (dot, i) {
			dot.addEventListener('click', function (event) {
				event.preventDefault();
				goTo(i, true);
			});
			dot.addEventListener('keydown', function (event) {
				if (event.key === 'ArrowRight') {
					event.preventDefault();
					goTo(i + 1, true);
					dots[((i + 1) % dots.length)].focus();
				} else if (event.key === 'ArrowLeft') {
					event.preventDefault();
					goTo(i - 1, true);
					dots[((i - 1 + dots.length) % dots.length)].focus();
				}
			});
		});

		root.addEventListener('pointerenter', function () {
			hovering = true;
			stop();
		});

		root.addEventListener('pointerleave', function () {
			hovering = false;
			start();
		});

		root.addEventListener('focusin', function () {
			userPaused = true;
			stop();
		});

		root.addEventListener('focusout', function (event) {
			if (!root.contains(event.relatedTarget)) {
				userPaused = false;
				start();
			}
		});

		document.addEventListener('visibilitychange', function () {
			if (document.hidden) {
				stop();
			} else {
				start();
			}
		});

		// Pause briefly after a touch swipe so autoplay doesn't fight the user.
		var resumeTimer = null;
		viewport.addEventListener('touchstart', function () {
			window.clearTimeout(resumeTimer);
			stop();
		}, { passive: true });

		viewport.addEventListener('touchend', function () {
			window.clearTimeout(resumeTimer);
			resumeTimer = window.setTimeout(start, 1400);
		}, { passive: true });

		// Mouse drag (desktop nicety); touch uses native scrolling.
		viewport.addEventListener('pointerdown', function (event) {
			if (event.pointerType !== 'mouse' || event.button !== 0) {
				return;
			}
			drag = { x: event.clientX, left: viewport.scrollLeft, moved: 0 };
			suppressClick = false;
			if (viewport.setPointerCapture) {
				try {
					viewport.setPointerCapture(event.pointerId);
				} catch (error) {
					/* ignore */
				}
			}
			stop();
		});

		viewport.addEventListener('pointermove', function (event) {
			if (!drag) {
				return;
			}
			var dx = event.clientX - drag.x;
			drag.moved = Math.max(drag.moved, Math.abs(dx));
			if (drag.moved > 6) {
				viewport.style.scrollSnapType = 'none';
				viewport.scrollLeft = drag.left - dx;
			}
		});

		function endDrag() {
			if (!drag) {
				return;
			}
			suppressClick = drag.moved > 8;
			drag = null;
			viewport.style.scrollSnapType = '';
			scrollToSlide(nearestIndex(), true);
			start();
		}

		viewport.addEventListener('pointerup', endDrag);
		viewport.addEventListener('pointercancel', endDrag);
		viewport.addEventListener('pointerleave', endDrag);

		viewport.addEventListener('click', function (event) {
			if (suppressClick) {
				event.preventDefault();
				event.stopPropagation();
				suppressClick = false;
			}
		}, true);

		var resizeFrame = 0;
		window.addEventListener('resize', function () {
			window.cancelAnimationFrame(resizeFrame);
			resizeFrame = window.requestAnimationFrame(function () {
				scrollToSlide(index, false);
			});
		});
	}

	/* ---------------------------------------------------------------------
	 * Local tabs (single app)
	 * ------------------------------------------------------------------ */

	function initTabs() {
		$$('[data-tabs]').forEach(function (group) {
			var buttons = $$('[data-tab]', group);
			if (!buttons.length) {
				return;
			}

			var scope = group.parentElement || document;
			var panels = $$('[data-panel]', scope);
			if (!panels.length) {
				panels = $$('[data-panel]');
			}

			function activate(key) {
				buttons.forEach(function (button) {
					var active = button.getAttribute('data-tab') === key;
					button.classList.toggle('is-active', active);
					button.setAttribute('aria-selected', active ? 'true' : 'false');
				});
				panels.forEach(function (panel) {
					var active = panel.getAttribute('data-panel') === key;
					panel.hidden = !active;
					panel.classList.toggle('is-active', active);
				});
			}

			var initial = buttons.filter(function (button) {
				return button.classList.contains('is-active');
			})[0] || buttons[0];

			activate(initial.getAttribute('data-tab'));

			group.addEventListener('click', function (event) {
				var button = closest(event.target, '[data-tab]');
				if (!button || !group.contains(button)) {
					return;
				}
				event.preventDefault();
				activate(button.getAttribute('data-tab'));
			});
		});
	}

	/* ---------------------------------------------------------------------
	 * Reactions (like / dislike)
	 * ------------------------------------------------------------------ */

	function actionSnapshot(scope) {
		return {
			like: $('[data-like][data-type="like"]', scope),
			dislike: $('[data-like][data-type="dislike"]', scope),
			likeCount: parseCount($('[data-like-count]', scope) && $('[data-like-count]', scope).textContent),
			dislikeCount: parseCount($('[data-dislike-count]', scope) && $('[data-dislike-count]', scope).textContent)
		};
	}

	function setActionState(button, on) {
		if (!button) {
			return;
		}
		var type = button.getAttribute('data-type') === 'dislike' ? 'dislike' : 'like';
		button.classList.toggle(type === 'like' ? 'is-liked' : 'is-disliked', on);
		button.setAttribute('aria-pressed', on ? 'true' : 'false');
	}

	function setActionCount(scope, type, value) {
		var el = $(type === 'like' ? '[data-like-count]' : '[data-dislike-count]', scope);
		if (el) {
			el.textContent = String(Math.max(0, value));
		}
	}

	function handleLike(button) {
		if (button.getAttribute('aria-busy') === 'true') {
			return;
		}
		if (!requireLogin()) {
			return;
		}

		var scope = closest(button, '[data-post-id]') || closest(button, '.av-post') || document;
		var postId = button.getAttribute('data-post-id') || (scope.getAttribute && scope.getAttribute('data-post-id'));
		var type = button.getAttribute('data-type') === 'dislike' ? 'dislike' : 'like';

		if (!postId) {
			return;
		}

		var snapshot = {
			like: closest(button, '[data-post-id]') ? $('[data-like][data-type="like"]', scope) : null,
			dislike: closest(button, '[data-post-id]') ? $('[data-like][data-type="dislike"]', scope) : null,
			likeCount: parseCount(($('[data-like-count]', scope) || {}).textContent),
			dislikeCount: parseCount(($('[data-dislike-count]', scope) || {}).textContent)
		};

		var stateKey = type === 'like' ? 'is-liked' : 'is-disliked';
		var otherKey = type === 'like' ? 'is-disliked' : 'is-liked';
		var turningOn = !button.classList.contains(stateKey);
		var otherButton = snapshot.like && snapshot.dislike ? (type === 'like' ? snapshot.dislike : snapshot.like) : null;

		// Optimistic update.
		button.classList.toggle(stateKey, turningOn);
		button.setAttribute('aria-pressed', turningOn ? 'true' : 'false');
		setActionCount(scope, type, (type === 'like' ? snapshot.likeCount : snapshot.dislikeCount) + (turningOn ? 1 : -1));

		if (turningOn && otherButton && otherButton.classList.contains(otherKey)) {
			otherButton.classList.remove(otherKey);
			otherButton.setAttribute('aria-pressed', 'false');
			var otherType = type === 'like' ? 'dislike' : 'like';
			var otherCount = type === 'like' ? snapshot.dislikeCount : snapshot.likeCount;
			setActionCount(scope, otherType, otherCount - 1);
		}

		button.setAttribute('aria-busy', 'true');

		ajax('appverse_like', { post_id: postId, type: type }).then(function (result) {
			button.removeAttribute('aria-busy');

			if (!result.json.success) {
				// 401 → session expired / not logged in.
				if (result.json.data && result.json.data.code === 'login') {
					handleAuthError(result);
					return;
				}
				setActionState(button, !turningOn);
				setActionCount(scope, type, type === 'like' ? snapshot.likeCount : snapshot.dislikeCount);
				if (otherButton) {
					setActionCount(scope, type === 'like' ? 'dislike' : 'like', type === 'like' ? snapshot.dislikeCount : snapshot.likeCount);
				}
				toast(messageOf(result), 'error');
				return;
			}

			var data = dataOf(result);
			if (snapshot.like) {
				setActionState(snapshot.like, typeof data.liked === 'boolean' ? data.liked : snapshot.like.classList.contains('is-liked'));
			}
			if (snapshot.dislike) {
				setActionState(snapshot.dislike, typeof data.disliked === 'boolean' ? data.disliked : snapshot.dislike.classList.contains('is-disliked'));
			}
			if (typeof data.likeCount !== 'undefined') {
				setActionCount(scope, 'like', data.likeCount);
			}
			if (typeof data.dislikeCount !== 'undefined') {
				setActionCount(scope, 'dislike', data.dislikeCount);
			}
			toast(turningOn ? (i18n.liked || '已点赞') : (i18n.unliked || '已取消'));
		}).catch(function () {
			button.removeAttribute('aria-busy');
			if (snapshot.like) {
				setActionState(snapshot.like, snapshot.like.classList.contains('is-liked'));
			}
			if (snapshot.dislike) {
				setActionState(snapshot.dislike, snapshot.dislike.classList.contains('is-disliked'));
			}
			setActionCount(scope, type, type === 'like' ? snapshot.likeCount : snapshot.dislikeCount);
			toast(i18n.error || '操作失败，请稍后重试', 'error');
		});
	}

	/* ---------------------------------------------------------------------
	 * Download modal + counter
	 * ------------------------------------------------------------------ */

	var modal = null;
	var modalPanel = null;
	var modalState = { app: null, lastFocus: null, open: false, busy: false, hideTimer: null };

	function findAppScope(button) {
		return closest(button, '.av-approw, .av-appcard, .av-post, .av-apphead, .av-downloadbar, [data-app]') || closest(button, '.av-card');
	}

	function appFromButton(button) {
		var scope = findAppScope(button);
		var icon = scope ? $('img.av-appicon__img', scope) : null;
		return {
			id: button.getAttribute('data-app-id') || (scope && scope.getAttribute('data-app-id')) || '',
			name: button.getAttribute('data-app-name') || (scope ? textOf(scope, '.av-approw__name, .av-appcard__name, .av-apphead__name, .av-downloadbar__name, .av-post__app-name') : ''),
			version: button.getAttribute('data-app-version') || (scope ? textOf(scope, '.av-approw__version, .av-post__app-meta, .av-downloadbar__meta') : ''),
			url: button.getAttribute('data-app-url') || button.getAttribute('data-url') || '',
			count: parseCount(button.getAttribute('data-app-count')),
			icon: icon ? icon.getAttribute('src') : ''
		};
	}

	function textOf(scope, selector) {
		var el = $(selector, scope);
		return el ? (el.textContent || '').replace(/\s+/g, ' ').trim() : '';
	}

	function ensureModal() {
		modal = modal || $('[data-modal]');
		if (!modal) {
			return false;
		}
		modalPanel = $('.av-modal__panel', modal) || $('[role="dialog"]', modal);
		return !!modalPanel;
	}

	function fillModal(app) {
		var iconBox = $('[data-modal-icon]', modal);
		var titleEl = $('[data-modal-title]', modal);
		var metaEl = $('[data-modal-meta]', modal);
		var continueEl = $('[data-modal-continue]', modal);

		if (iconBox) {
			iconBox.textContent = '';
			if (app.icon) {
				var img = document.createElement('img');
				img.src = app.icon;
				img.alt = app.name || '';
				img.setAttribute('decoding', 'async');
				img.setAttribute('aria-hidden', 'true');
				iconBox.appendChild(img);
			}
		}

		setText(titleEl, app.name || i18n.download || '下载应用');

		var metaParts = [];
		if (app.version) {
			metaParts.push('版本 ' + app.version);
		}
		if (app.count) {
			metaParts.push('已有 ' + formatCount(app.count) + ' 次下载');
		}
		setText(metaEl, metaParts.join(' · '));

		if (continueEl) {
			continueEl.textContent = isLoggedIn() ? (i18n.continue || '继续下载') : (i18n.login || '登录后才能操作');
			if (continueEl.setAttribute) {
				continueEl.setAttribute('href', isLoggedIn() ? (app.url || '#') : (cfg.loginUrl || '#'));
			}
			continueEl.classList.toggle('is-login', !isLoggedIn());
		}
	}

	function openModal(app) {
		if (!ensureModal()) {
			// No modal in this page: behave like a direct link.
			if (app.url && app.url !== '#') {
				window.location.href = app.url;
			}
			return;
		}

		modalState.app = app;
		modalState.lastFocus = document.activeElement;
		window.clearTimeout(modalState.hideTimer);

		fillModal(app);
		modal.hidden = false;
		document.documentElement.classList.add('av-no-scroll');

		window.requestAnimationFrame(function () {
			modal.classList.add('is-open');
			if (modalPanel && modalPanel.focus) {
				modalPanel.setAttribute('tabindex', '-1');
				modalPanel.focus({ preventScroll: true });
			}
		});

		modalState.open = true;
	}

	function closeModal() {
		if (!modal || !modalState.open) {
			return;
		}
		modal.classList.remove('is-open');
		modalState.open = false;
		document.documentElement.classList.remove('av-no-scroll');

		window.clearTimeout(modalState.hideTimer);
		modalState.hideTimer = window.setTimeout(function () {
			if (!modalState.open) {
				modal.hidden = true;
			}
		}, 300);

		if (modalState.lastFocus && modalState.lastFocus.focus) {
			try {
				modalState.lastFocus.focus({ preventScroll: true });
			} catch (error) {
				modalState.lastFocus.focus();
			}
		}
		modalState.app = null;
	}

	function syncDownloadCounts(appId, count) {
		if (!appId) {
			return;
		}
		$$('[data-download]').forEach(function (button) {
			if (button.getAttribute('data-app-id') !== String(appId)) {
				return;
			}
			button.setAttribute('data-app-count', String(count));
			var scope = findAppScope(button);
			var countEl = scope ? $('.av-download-count', scope) : null;
			if (countEl) {
				countEl.textContent = '↓' + formatCount(count);
			}
		});
	}

	function handleDownload(button) {
		var app = appFromButton(button);
		openModal(app);
	}

	function handleDownloadContinue(event) {
		var continueEl = event.target.closest ? event.target.closest('[data-modal-continue]') : null;
		if (!continueEl) {
			return;
		}
		event.preventDefault();

		var app = modalState.app;
		if (!app || !app.id || modalState.busy) {
			return;
		}

		// Guests are asked to sign in first (the modal shows the login CTA).
		if (!isLoggedIn()) {
			if (cfg.loginUrl) {
				window.location.href = cfg.loginUrl;
			}
			return;
		}

		modalState.busy = true;
		if (continueEl.classList) {
			continueEl.setAttribute('aria-busy', 'true');
		}

		ajax('appverse_download', { app_id: app.id }).then(function (result) {
			modalState.busy = false;
			if (continueEl.classList) {
				continueEl.removeAttribute('aria-busy');
			}

			var data = dataOf(result);
			var target = data.url || app.url || '';

			if (!result.json.success) {
				toast(messageOf(result), 'error');
				return;
			}

			if (typeof data.count !== 'undefined') {
				syncDownloadCounts(app.id, parseInt(data.count, 10) || 0);
			}

			toast(i18n.downloading || '开始下载…');
			closeModal();

			if (target && target !== '#') {
				window.setTimeout(function () {
					window.location.href = target;
				}, 320);
			}
		}).catch(function () {
			modalState.busy = false;
			if (continueEl.classList) {
				continueEl.removeAttribute('aria-busy');
			}
			toast(i18n.error || '操作失败，请稍后重试', 'error');
		});
	}

	/* ---------------------------------------------------------------------
	 * Check-in
	 * ------------------------------------------------------------------ */

	function markCheckedIn(points) {
		$$('[data-checkin]').forEach(function (button) {
			button.classList.add('is-done');
			button.setAttribute('data-checked', '1');
			button.setAttribute('aria-disabled', 'true');
			if (button.tagName === 'BUTTON') {
				button.disabled = true;
			}
			var label = button.classList.contains('av-checkin') ? '已签到' : '今日已签到';
			setText(button, label);
		});

		var pointsEl = $('.av-checkin-hero__points-num');
		if (pointsEl && typeof points !== 'undefined') {
			pointsEl.textContent = String(points);
		}

		$$('.av-day.is-today').forEach(function (day) {
			day.classList.add('is-checked');
		});
	}

	function handleCheckin(button) {
		if (button.getAttribute('aria-busy') === 'true') {
			return;
		}
		if (button.classList.contains('is-done') || button.getAttribute('data-checked') === '1') {
			toast(i18n.checked || '今天已经签到啦');
			return;
		}
		if (!isLoggedIn()) {
			requireLogin();
			return;
		}

		button.setAttribute('aria-busy', 'true');

		ajax('appverse_checkin', {}).then(function (result) {
			button.removeAttribute('aria-busy');
			var data = dataOf(result);

			if (result.json.success) {
				markCheckedIn(data.points);
				toast(data.message || i18n.checkin || '签到成功，积分 +5');
				return;
			}

			if (data.code === 'checked') {
				markCheckedIn(data.points);
				toast(data.message || i18n.checked || '今天已经签到啦');
				return;
			}

			if (data.code === 'login') {
				handleAuthError(result);
				return;
			}

			toast(messageOf(result), 'error');
		}).catch(function () {
			button.removeAttribute('aria-busy');
			toast(i18n.error || '操作失败，请稍后重试', 'error');
		});
	}

	/* ---------------------------------------------------------------------
	 * Modal wiring
	 * ------------------------------------------------------------------ */

	function initModal() {
		if (!ensureModal()) {
			return;
		}

		modal.addEventListener('click', function (event) {
			if (closest(event.target, '[data-modal-close]') || event.target === modal) {
				closeModal();
			}
		});

		modal.addEventListener('click', handleDownloadContinue);

		document.addEventListener('keydown', function (event) {
			if (!modalState.open) {
				return;
			}
			if (event.key === 'Escape' || event.key === 'Esc') {
				event.preventDefault();
				closeModal();
				return;
			}
			if (event.key !== 'Tab') {
				return;
			}

			var focusable = $$('a[href], button:not([disabled]), input:not([disabled]), textarea:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])', modalPanel).filter(function (el) {
				return el.offsetParent !== null;
			});
			if (!focusable.length) {
				return;
			}
			var first = focusable[0];
			var last = focusable[focusable.length - 1];
			if (event.shiftKey && (document.activeElement === first || document.activeElement === modalPanel)) {
				event.preventDefault();
				last.focus();
			} else if (!event.shiftKey && document.activeElement === last) {
				event.preventDefault();
				first.focus();
			}
		});
	}

	/* ---------------------------------------------------------------------
	 * Delegated events
	 * ------------------------------------------------------------------ */

	function initDelegates() {
		document.addEventListener('click', function (event) {
			// Small helpers: any element carrying data-toast shows its message.
			var toastTrigger = closest(event.target, '[data-toast]');
			if (toastTrigger) {
				var message = toastTrigger.getAttribute('data-toast');
				if (message) {
					toast(message);
				}
				if (toastTrigger.tagName === 'BUTTON' && toastTrigger.type === 'button') {
					event.preventDefault();
				}
			}

			var likeButton = closest(event.target, '[data-like]');
			if (likeButton) {
				event.preventDefault();
				handleLike(likeButton);
				return;
			}

			var checkinButton = closest(event.target, '[data-checkin]');
			if (checkinButton) {
				event.preventDefault();
				handleCheckin(checkinButton);
				return;
			}

			var downloadButton = closest(event.target, '[data-download]');
			if (downloadButton) {
				event.preventDefault();
				handleDownload(downloadButton);
			}
		});
	}

	/* ---------------------------------------------------------------------
	 * Search focus (inner-page search links land on the results form)
	 * ------------------------------------------------------------------ */

	function initSearchFocus() {
		if (!/[?&]s=/.test(window.location.search)) {
			return;
		}
		var input = $('.av-searchbar--page .av-searchbar__input');
		if (!input) {
			return;
		}
		window.requestAnimationFrame(function () {
			input.focus({ preventScroll: true });
			var value = input.value;
			if (input.setSelectionRange && typeof value === 'string') {
				input.setSelectionRange(value.length, value.length);
			}
		});
	}

	/* ---------------------------------------------------------------------
	 * Boot
	 * ------------------------------------------------------------------ */

	function boot() {
		toastEl = $('.av-toast[data-toast]');

		$$('[data-carousel]').forEach(initCarousel);
		initTabs();
		initModal();
		initDelegates();
		initSearchFocus();
	}

	window.Appverse = window.Appverse || {};
	window.Appverse.toast = toast;
	window.Appverse.openModal = openModal;
	window.Appverse.closeModal = closeModal;
	window.Appverse.config = cfg;

	if (document.readyState === 'loading') {
		document.addEventListener('DOMContentLoaded', boot);
	} else {
		boot();
	}
})(window, document);
