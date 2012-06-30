{page title="Year 2010" meta_description="Testing, I'm a bit lost now"}
{require name="budget"}
<div class="toggle">
{foreach from=$data key=year item=budget}
	<span>{$year}</span>
{/foreach}
</div>
<div class="wrapper">
{foreach from=$data key=year item=budget}
<div id="year-{$year}" class="year-wrapper">
	<div class="year">{$year}</div>
	<ul class="expenses chart">
		<li class="total">
			<span class="title">Expenses</span>
			<span class="value">{$budget.expenses_count|number_format:2:".":","}</span>
		</li>
	{foreach from=$budget.expenses key=name item=value}
		<li class="division d{$value.divisions|round}" style="height: {math equation="(x * y)" x=$value.divisions y=60}px">
			<span class="title">{$name}</span>
			<span class="value">{$value.amount|number_format:2:".":","}</span>
		</li>
	{/foreach}
	</ul>
	<ul class="revenues chart">
		<li class="total">
			<span class="title">Revenues</span>
			<span class="value">{$budget.revenues_count|number_format:2:".":","}</span>
		</li>
	{foreach from=$budget.revenues key=name item=value}
		<li class="division d{$value.divisions|round}" style="height: {math equation="(x * y)" x=$value.divisions y=60}px">
			<span class="title">{$name}</span>
			<span class="value">{$value.amount|number_format:2:".":","}</span>
		</li>
	{/foreach}
	</ul>
	<div class="difference {if $budget.revenues_count > $budget.expenses_count }profit{else}loss{/if}">{$budget.difference|number_format:2:".":","}</div>
</div>
{/foreach}
</div>