/**
 * Authority Signal Score Calculator
 *
 * Interactive 5-question assessment that calculates an Authority Signal Score
 * across the 4 layers of the Authority Stack (Borrowed, Articulated, Earned,
 * Demonstrated). All calculation is client-side; no data is sent or stored.
 *
 * Pattern: IIFE + ES6 class (matches home.js)
 * Dependencies: None (vanilla JS, no jQuery)
 *
 * @package Guestify
 * @version 1.0.0
 */

(function () {
	'use strict';

	class AuthorityScoreCalculator {
		constructor() {
			this.container = document.getElementById( 'gfy-authority-calculator' );
			if ( ! this.container ) return;

			// ── Configuration ─────────────────────────────────────────
			this.totalQuestions = 5;
			this.currentStep = 0;
			this.answers = new Array( this.totalQuestions ).fill( 2 ); // default mid-value

			// Points awarded per answer value (0-4) for each question
			this.scoringTable = [
				[ 0, 5, 12, 20, 25 ],   // Q1 — Borrowed Authority
				[ 0, 5, 12, 20, 25 ],   // Q2 — Borrowed + Articulated
				[ 0, 5, 10, 18, 25 ],   // Q3 — Articulated Authority
				[ 0, 4,  8, 14, 18 ],   // Q4 — Earned Authority
				[ 0, 3,  7, 12, 17 ]    // Q5 — Demonstrated Authority
			];

			this.maxRawScore = 110;

			// Which questions contribute to which layers (with weights)
			this.layerMap = {
				borrowed:     { questions: [ 0, 1 ], weights: [ 1.0, 0.5 ] },
				articulated:  { questions: [ 1, 2 ], weights: [ 0.5, 1.0 ] },
				earned:       { questions: [ 3 ],    weights: [ 1.0 ] },
				demonstrated: { questions: [ 4 ],    weights: [ 1.0 ] }
			};

			// Max raw points per layer (for normalization to 0-25 each)
			this.layerMaxRaw = {
				borrowed:     37.5,
				articulated:  37.5,
				earned:       18,
				demonstrated: 17
			};

			// Score tier definitions
			this.tiers = [
				{
					min: 0,   max: 25,  name: 'Invisible',         cssClass: 'invisible',
					desc: 'Your authority signals are nearly invisible to AI and decision-makers.',
					recommendation: 'The highest-leverage move right now: get endorsed by credible voices through strategic podcast interviews. A single well-placed host introduction creates more authority signal than months of content creation.',
					ctaText: 'Guestify helps experts build their Authority Graph from scratch. Start with strategic podcast discovery.',
					ctaBtnText: 'Start Free Trial',
					ctaBtnHref: '/start-free-trial'
				},
				{
					min: 26,  max: 50,  name: 'Emerging',           cssClass: 'emerging',
					desc: 'You have the foundation, but your authority graph has gaps that AI systems notice.',
					recommendation: 'Focus on building consistent connections between your expertise and the audiences who need it. Your weakest authority layer is where the biggest opportunity lives. Strategic podcast interviews can strengthen multiple layers simultaneously.',
					ctaText: 'Guestify helps emerging experts find the right podcasts and build the relationships that fill authority gaps.',
					ctaBtnText: 'Start Free Trial',
					ctaBtnHref: '/start-free-trial'
				},
				{
					min: 51,  max: 75,  name: 'Growing',            cssClass: 'growing',
					desc: 'Your authority is building momentum. The next step is strategic: strengthen your weakest layer.',
					recommendation: 'You have real authority signals working for you. The key now is strategic compounding: choosing podcast appearances that strengthen your weakest layer while reinforcing your strongest. Quality over volume matters most at this stage.',
					ctaText: 'Guestify accelerates authority compounding by finding the right podcasts and managing host relationships at scale.',
					ctaBtnText: 'See How It Works',
					ctaBtnHref: '/how-it-works'
				},
				{
					min: 76,  max: 100, name: 'Authority Leader',   cssClass: 'leader',
					desc: 'Your authority graph is strong and interconnected. Maintain momentum by deepening relationships.',
					recommendation: 'You are where most experts aspire to be. Maintain your authority graph by deepening existing host relationships, expanding into adjacent topic ecosystems, and ensuring your message stays consistent as your reach grows. The biggest risk at this stage is complacency.',
					ctaText: 'Experts at your level use Guestify to maintain their authority graph and systematize the relationship-building that got them here.',
					ctaBtnText: 'Explore Guestify',
					ctaBtnHref: '/how-it-works'
				}
			];

			// Reduced motion preference
			this.prefersReducedMotion = window.matchMedia( '(prefers-reduced-motion: reduce)' ).matches;

			// DOM cache
			this.panels       = null;
			this.progressFill = null;
			this.stepDots     = null;
			this.calcSection  = null;
			this.resultsPanel = null;
			this.sliders      = null;
			this.backBtn      = null;
			this.nextBtn      = null;

			// Initialize
			if ( document.readyState === 'loading' ) {
				document.addEventListener( 'DOMContentLoaded', () => this.init() );
			} else {
				this.init();
			}
		}

		// ── Initialization ───────────────────────────────────────────

		init() {
			this.cacheDom();
			this.bindEvents();
			this.showStep( 0 );
		}

		cacheDom() {
			this.panels       = this.container.querySelectorAll( '.gfy-score-calc__panel' );
			this.progressFill = this.container.querySelector( '.gfy-score-calc__progress-fill' );
			this.stepDots     = this.container.querySelectorAll( '.gfy-score-calc__step-dot' );
			this.calcSection  = this.container.querySelector( '.gfy-score-calc' );
			this.resultsPanel = this.container.querySelector( '.gfy-score-results' );
			this.sliders      = this.container.querySelectorAll( '.gfy-score-calc__slider' );
			this.backBtn      = this.container.querySelector( '[data-action="back"]' );
			this.nextBtn      = this.container.querySelector( '[data-action="next"]' );
			this.retakeBtn    = this.container.querySelector( '[data-action="retake"]' );
		}

		bindEvents() {
			// Slider input events
			this.sliders.forEach( ( slider, index ) => {
				slider.addEventListener( 'input', ( e ) => {
					this.answers[ index ] = parseInt( e.target.value, 10 );
					this.updateSliderLabel( index );
				} );
			} );

			// Navigation
			if ( this.backBtn ) {
				this.backBtn.addEventListener( 'click', () => this.prevStep() );
			}
			if ( this.nextBtn ) {
				this.nextBtn.addEventListener( 'click', () => this.nextStep() );
			}

			// Retake
			if ( this.retakeBtn ) {
				this.retakeBtn.addEventListener( 'click', () => this.retake() );
			}
		}

		// ── Step Navigation ──────────────────────────────────────────

		showStep( step ) {
			this.currentStep = step;

			// Toggle panel visibility
			this.panels.forEach( ( panel, i ) => {
				panel.classList.toggle( 'gfy-score-calc__panel--active', i === step );
			} );

			// Update progress bar
			var progressPct = ( ( step + 1 ) / this.totalQuestions ) * 100;
			if ( this.progressFill ) {
				this.progressFill.style.width = progressPct + '%';
			}

			// Update step dots
			this.stepDots.forEach( ( dot, i ) => {
				dot.classList.toggle( 'gfy-score-calc__step-dot--active', i === step );
				dot.classList.toggle( 'gfy-score-calc__step-dot--complete', i < step );
			} );

			// Back button visibility
			if ( this.backBtn ) {
				this.backBtn.style.visibility = step === 0 ? 'hidden' : 'visible';
			}

			// Next button text
			if ( this.nextBtn ) {
				this.nextBtn.textContent = step === this.totalQuestions - 1
					? 'See My Score'
					: 'Next';
			}

			// Update slider aria-valuetext
			this.updateSliderAria( step );
		}

		nextStep() {
			if ( this.currentStep < this.totalQuestions - 1 ) {
				this.showStep( this.currentStep + 1 );
			} else {
				this.calculateAndShowResults();
			}
		}

		prevStep() {
			if ( this.currentStep > 0 ) {
				this.showStep( this.currentStep - 1 );
			}
		}

		// ── Slider Helpers ───────────────────────────────────────────

		updateSliderLabel( questionIndex ) {
			var panel = this.panels[ questionIndex ];
			if ( ! panel ) return;

			var labels = panel.querySelectorAll( '.gfy-score-calc__slider-label' );
			var val = this.answers[ questionIndex ];

			labels.forEach( ( label, i ) => {
				label.classList.toggle( 'gfy-score-calc__slider-label--active', i === val );
			} );

			// Also update aria-valuetext
			this.updateSliderAria( questionIndex );
		}

		updateSliderAria( questionIndex ) {
			var panel = this.panels[ questionIndex ];
			if ( ! panel ) return;

			var slider = panel.querySelector( '.gfy-score-calc__slider' );
			var labels = panel.querySelectorAll( '.gfy-score-calc__slider-label' );
			var val = parseInt( slider.value, 10 );

			if ( labels[ val ] ) {
				slider.setAttribute( 'aria-valuetext', labels[ val ].textContent.trim() );
			}
		}

		// ── Score Calculation ────────────────────────────────────────

		calculateAndShowResults() {
			// Calculate raw score
			var rawTotal = 0;
			this.answers.forEach( ( val, i ) => {
				rawTotal += this.scoringTable[ i ][ val ];
			} );

			// Normalize to 0-100
			var totalScore = Math.round( ( rawTotal / this.maxRawScore ) * 100 );

			// Calculate layer scores (each normalized to 0-25)
			var layerScores = {};
			for ( var layer in this.layerMap ) {
				var config = this.layerMap[ layer ];
				var layerRaw = 0;

				config.questions.forEach( ( qIndex, i ) => {
					layerRaw += this.scoringTable[ qIndex ][ this.answers[ qIndex ] ] * config.weights[ i ];
				} );

				layerScores[ layer ] = Math.round( ( layerRaw / this.layerMaxRaw[ layer ] ) * 25 );
			}

			// Determine tier
			var tier = this.getTier( totalScore );

			// Find weakest layer
			var weakestLayer = this.getWeakestLayer( layerScores );

			// Render
			this.renderResults( totalScore, layerScores, tier, weakestLayer );
		}

		getTier( score ) {
			for ( var i = 0; i < this.tiers.length; i++ ) {
				if ( score >= this.tiers[ i ].min && score <= this.tiers[ i ].max ) {
					return this.tiers[ i ];
				}
			}
			return this.tiers[ this.tiers.length - 1 ];
		}

		getWeakestLayer( layerScores ) {
			var weakest = null;
			var lowest = 26;
			var layerNames = {
				borrowed: 'Borrowed Authority',
				articulated: 'Articulated Authority',
				earned: 'Earned Authority',
				demonstrated: 'Demonstrated Authority'
			};

			for ( var layer in layerScores ) {
				if ( layerScores[ layer ] < lowest ) {
					lowest = layerScores[ layer ];
					weakest = layerNames[ layer ];
				}
			}
			return weakest;
		}

		// ── Results Rendering ────────────────────────────────────────

		renderResults( totalScore, layerScores, tier, weakestLayer ) {
			// Hide calculator, show results
			if ( this.calcSection ) {
				this.calcSection.style.display = 'none';
			}
			this.resultsPanel.classList.add( 'gfy-score-results--visible' );

			// Set gauge color based on tier
			var gaugeCircle = this.resultsPanel.querySelector( '.gfy-score-results__gauge-circle' );
			if ( gaugeCircle ) {
				var tierColors = {
					invisible: 'var(--gfy-error-500, #ef4444)',
					emerging:  'var(--gfy-warning-500, #f59e0b)',
					growing:   'var(--gfy-secondary-500, #2B7A4C)',
					leader:    'var(--gfy-primary-800, #1B365D)'
				};
				gaugeCircle.style.stroke = tierColors[ tier.cssClass ] || tierColors.emerging;
			}

			// Animate radial gauge
			this.animateGauge( totalScore );

			// Set tier label
			var tierEl = this.resultsPanel.querySelector( '.gfy-score-results__tier' );
			if ( tierEl ) {
				tierEl.textContent = tier.name;
				// Remove any existing tier class
				tierEl.className = 'gfy-score-results__tier gfy-score-results__tier--' + tier.cssClass;
			}

			// Set tier description
			var tierDescEl = this.resultsPanel.querySelector( '.gfy-score-results__tier-desc' );
			if ( tierDescEl ) {
				tierDescEl.textContent = tier.desc;
			}

			// Set layer bar widths
			for ( var layer in layerScores ) {
				var layerEl = this.resultsPanel.querySelector( '[data-layer="' + layer + '"]' );
				if ( ! layerEl ) continue;

				var fillEl = layerEl.querySelector( '.gfy-score-results__layer-fill' );
				var valueEl = layerEl.querySelector( '.gfy-score-results__layer-value' );

				if ( valueEl ) {
					valueEl.textContent = layerScores[ layer ] + ' / 25';
				}

				if ( fillEl ) {
					var pct = ( layerScores[ layer ] / 25 ) * 100;
					// Use requestAnimationFrame for smooth transition trigger
					requestAnimationFrame( () => {
						fillEl.style.width = pct + '%';
					} );
				}
			}

			// Set recommendation
			var recText = this.resultsPanel.querySelector( '.gfy-score-results__recommendation-text' );
			if ( recText ) {
				var recommendation = tier.recommendation;
				if ( weakestLayer ) {
					recommendation = 'Your weakest layer is ' + weakestLayer + '. ' + recommendation;
				}
				recText.textContent = recommendation;
			}

			// Set CTA
			var ctaText = this.resultsPanel.querySelector( '.gfy-score-results__cta-text' );
			var ctaBtn = this.resultsPanel.querySelector( '.gfy-score-results__cta-btn' );
			if ( ctaText ) {
				ctaText.textContent = tier.ctaText;
			}
			if ( ctaBtn ) {
				ctaBtn.textContent = tier.ctaBtnText;
				ctaBtn.href = tier.ctaBtnHref;
			}

			// Scroll results into view
			this.resultsPanel.scrollIntoView( { behavior: this.prefersReducedMotion ? 'auto' : 'smooth', block: 'start' } );
		}

		// ── Gauge Animation ──────────────────────────────────────────

		animateGauge( score ) {
			var circle = this.resultsPanel.querySelector( '.gfy-score-results__gauge-circle' );
			var valueEl = this.resultsPanel.querySelector( '.gfy-score-results__gauge-value' );

			if ( ! circle || ! valueEl ) return;

			var radius = 54;
			var circumference = 2 * Math.PI * radius;
			var offset = circumference - ( score / 100 ) * circumference;

			// Set initial state
			circle.style.strokeDasharray = circumference;
			circle.style.strokeDashoffset = circumference;

			if ( this.prefersReducedMotion ) {
				// Skip animation — show final state immediately
				circle.style.strokeDashoffset = offset;
				valueEl.textContent = score;
				return;
			}

			// Trigger animation after a frame
			requestAnimationFrame( () => {
				circle.style.strokeDashoffset = offset;
			} );

			// Animate number count-up
			this.animateCountUp( valueEl, 0, score, 1500 );
		}

		animateCountUp( element, start, end, duration ) {
			var startTime = performance.now();

			var step = ( currentTime ) => {
				var elapsed = currentTime - startTime;
				var progress = Math.min( elapsed / duration, 1 );
				// Ease-out cubic
				var eased = 1 - Math.pow( 1 - progress, 3 );
				element.textContent = Math.round( start + ( end - start ) * eased );

				if ( progress < 1 ) {
					requestAnimationFrame( step );
				}
			};

			requestAnimationFrame( step );
		}

		// ── Retake ───────────────────────────────────────────────────

		retake() {
			// Reset answers
			this.answers = new Array( this.totalQuestions ).fill( 2 );

			// Reset sliders to mid-value
			this.sliders.forEach( ( slider, index ) => {
				slider.value = 2;
				this.updateSliderLabel( index );
			} );

			// Reset layer bar widths
			this.resultsPanel.querySelectorAll( '.gfy-score-results__layer-fill' ).forEach( ( fill ) => {
				fill.style.width = '0';
			} );

			// Hide results, show calculator
			this.resultsPanel.classList.remove( 'gfy-score-results--visible' );
			if ( this.calcSection ) {
				this.calcSection.style.display = '';
			}

			// Go to first step
			this.showStep( 0 );

			// Scroll calculator into view
			this.container.scrollIntoView( { behavior: this.prefersReducedMotion ? 'auto' : 'smooth', block: 'start' } );
		}
	}

	// Initialize
	window.GuestifyAuthorityScore = new AuthorityScoreCalculator();

})();
