// Define your custom Backbone view for the checkbox// Extend bp.Views.FormSubmit
bp.Views.FormSubmit = bp.Views.FormSubmit.extend({

    render: function(){
        this.$el.append( this.renderCheckbox() );
    },

    renderCheckbox: function() {
        // Create and append the checkbox
        this.checkbox = new bp.Views.ActivityInput({
            type: 'checkbox',
            id: 'anonymously-post',
            className: 'anonymously-post',
            name: 'anonymously-post',
            value: '1'
        });
  
        var checkboxMain = document.createElement('div');
        checkboxMain.classList.add('anonymously-post-main');
  
        var checkboxLabel = document.createElement('label');
        checkboxLabel.textContent = paf.post_anonymously_label;
        checkboxMain.appendChild(checkboxLabel);
        
        var checkboxWrapper = document.createElement('div');
        checkboxWrapper.classList.add('anonymously-post-wrap');
        
        checkboxWrapper.appendChild(this.checkbox.el);
        
        var checkboxknobs = document.createElement('div');
        checkboxknobs.classList.add('anonymously-post-knobs'); 
        checkboxWrapper.appendChild(checkboxknobs);
  
        var checkboxlayer = document.createElement('div');
        checkboxlayer.classList.add('anonymously-post-layer'); 
        checkboxWrapper.appendChild(checkboxlayer);
  
        checkboxMain.appendChild(checkboxWrapper);
        return this.checkbox.el = checkboxMain;
    }
});