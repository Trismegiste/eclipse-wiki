describe('Session report', () => {

    it('shows the session report', () => {
        cy.visit('/')
        cy.get('main .icon-session-report').click()
        cy.get('#gallery_selection_export').click()
    })

})