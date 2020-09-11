<?php

namespace Tests\Browser\SyncHistory;

use Livewire\Livewire;
use Laravel\Dusk\Browser;
use Tests\Browser\TestCase;
use Tests\Browser\Defer\Component as DeferComponent;

class Test extends TestCase
{
    public function test_route_bound_properties_are_synced_with_browser_history()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(route('sync-history', ['step' => 1], false))
                ->waitForText('Step 1 Active');

            $browser->waitForLivewire()->click('@step-2')
                ->assertRouteIs('sync-history', ['step' => 2]);

            $browser
                ->back()
                ->assertRouteIs('sync-history', ['step' => 1]);
        });
    }

    public function test_that_query_bound_properties_are_synced_with_browser_history()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(route('sync-history', ['step' => 1], false))
                ->waitForText('Help is currently disabled')
                ->assertQueryStringHas('showHelp', 'false');

            $browser->waitForLivewire()->click('@toggle-help')
                ->assertQueryStringHas('showHelp', 'true');

            $browser->waitForLivewire()->click('@toggle-help')
                ->waitForText('Help is currently disabled')
                ->assertQueryStringHas('showHelp', 'false');

            $browser->back()
                ->waitForText('Help is currently enabled')
                ->assertQueryStringHas('showHelp', 'true');

            $browser->back()
                ->waitForText('Help is currently disabled')
                ->assertQueryStringHas('showHelp', 'false');
        });
    }

    public function test_that_route_and_query_bound_properties_can_both_be_synced_with_browser_history()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(route('sync-history', ['step' => 1], false))
                ->waitForText('Step 1 Active')
                ->waitForText('Help is currently disabled')
                ->assertQueryStringHas('showHelp', 'false');

            $browser->waitForLivewire()->click('@toggle-help')
                ->assertQueryStringHas('showHelp', 'true');

            $browser->waitForLivewire()->click('@step-2')
                ->assertRouteIs('sync-history', ['step' => 2])
                ->assertQueryStringHas('showHelp', 'true');

            $browser->waitForLivewire()->click('@toggle-help')
               ->assertQueryStringHas('showHelp', 'false');

            $browser->back()
                ->waitForText('Help is currently enabled')
                ->assertQueryStringHas('showHelp', 'true')
                ->assertRouteIs('sync-history', ['step' => 2]);

            $browser->back()
                ->waitForText('Step 1 Active')
                ->assertRouteIs('sync-history', ['step' => 1])
                ->assertQueryStringHas('showHelp', 'true');

            $browser->back()
               ->waitForText('Help is currently disabled')
               ->assertQueryStringHas('showHelp', 'false');
        });
    }

    public function test_that_query_updates_from_child_components_can_coexist()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(route('sync-history', ['step' => 1], false))
                ->waitForText('Step 1 Active')
                ->waitForText('Dark mode is currently disabled')
                ->assertQueryStringHas('darkmode', 'false');

            $browser->waitForLivewire()->click('@toggle-darkmode')
                ->assertQueryStringHas('darkmode', 'true');

            $browser->waitForLivewire()->click('@step-2')
                ->assertRouteIs('sync-history', ['step' => 2])
                ->assertQueryStringHas('darkmode', 'true');

            $browser->waitForLivewire()->click('@toggle-darkmode')
                ->assertQueryStringHas('darkmode', 'false');

            $browser->back()
                ->waitForText('Dark mode is currently enabled')
                ->assertQueryStringHas('darkmode', 'true')
                ->assertRouteIs('sync-history', ['step' => 2]);

            $browser->back()
                ->waitForText('Step 1 Active')
                ->assertRouteIs('sync-history', ['step' => 1])
                ->assertQueryStringHas('darkmode', 'true');

            $browser->back()
                ->assertRouteIs('sync-history', ['step' => 1])
                ->waitForText('Dark mode is currently disabled')
                ->assertQueryStringHas('darkmode', 'false');
        });
    }

    public function test_that_if_a_parameter_comes_in_from_the_route_and_doesnt_have_a_matching_property_things_dont_break()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(route('sync-history-without-mount', ['id' => 1], false))
                ->assertSeeIn('@output', '1')
                ->waitForLivewire()->click('@button')
                ->assertSeeIn('@output', '5');
        });
    }

    public function test_that_we_are_not_leaking_old_components_into_history_state_on_refresh()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit(route('sync-history', ['step' => 1], false))
                ->assertScript('Object.keys(window.history.state.livewire).length', 2)
                ->refresh()
                ->assertScript('Object.keys(window.history.state.livewire).length', 2);
        });
    }

    public function test_that_we_are_not_setting_history_state_unless_there_are_route_bound_params_or_query_string_properties()
    {
        $this->browse(function ($browser) {
            Livewire::visit($browser, DeferComponent::class)
                ->assertScript('history.state', null)
            ;
        });
    }
}
