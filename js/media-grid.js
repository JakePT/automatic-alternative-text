'use strict';

( function( $ ) {
	var originalView = wp.media.view.Attachment.Details.TwoColumn.extend();

	wp.media.view.Attachment.Details.TwoColumn = originalView.extend(
		{
			events: function() {
				return _.extend(
					{
						'click .button-aat-describe': this.onDescribe
					},
					originalView.prototype.events
				);
			},
			render: function() {
				originalView.prototype.render.apply( this, arguments );

				this.$( '#alt-text-description' ).append( wp.template( 'aat-describe-button' ) );
			},
			onDescribe: function( event ) {
				wp.apiRequest(
					{
						namespace: 'aat/v1',
						endpoint: 'describe/' + this.model.get( 'id' ),
						addNonceHeader: true,
						beforeSend: function() {
							this.$el.addClass( 'save-waiting' );
							this.$( '[data-setting="alt"] input' ).prop( 'disabled', true );
							this.$( event.target ).prop( 'disabled', true );
						}.bind( this ),
						success: function( response ) {
							if ( response.data ) {
								this.save( 'alt', response.data );
							}
						}.bind( this ),
						complete: function() {
							this.$el.removeClass( 'save-waiting' );
							this.$( '[data-setting="alt"] input' ).prop( 'disabled', false );
							this.$( event.target ).prop( 'disabled', false );
							this.render();
						}.bind( this )
					}
				);
			},
		}
	);
} )( jQuery )