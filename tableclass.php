<?php
if(!defined('METAL_LIBRARY_HTML_TABLE_CLASS'))
{
	define('METAL_LIBRARY_HTML_TABLE_CLASS',1);

/*
 *
 * @(#) $Id: tableclass.class,v 1.23 2007/01/30 05:00:36 mlemos Exp $
 *
 */

class table_class
{
	
	/*
	 * Public variables
	 *
	 */
	var $center=1;
	var $border=1;
	var $width='';
	var $style='';
	var $class='';
	var $page=0;
	var $rowsperpage=10;
	var $totalrows=0;
	var $headerlistingrowrange=1;
	var $footerlistingrowrange=0;
	var $headerlistingpages=0;
	var $footerlistingpages=1;
	var $listpages=3;
	var $pagevariable='page';
	var $pagelinkurl='';
	var $pagelinkvalues=array();
	var $pagelinkvaluesstring='';
	var $rangelinkseparator=' | ';
	var $firstprefix='<<';
	var $previousprefix='<';
	var $nextsuffix='>';
	var $lastsuffix='>>';
	var $rangeinfirstlast=1;
	var $rangeinpreviousnext=1;
	
	
	/*
	 * Public functions
	 *
	 */
	Function encodeoutput($output)
	{
		return HtmlEntities($output);
	}
	
	Function fetchcolumn(&$columndata)
	{
		return 0;
	}
	
	Function outputcolumns($row)
	{
		$columndata=array('row'=>$row,'column'=>0);
		$output='';
		for(;;)
		{
			$columndata['data']='';
			$columndata['header']=0;
			$columndata['backgroundcolor']='';
			$columndata['width']='';
			$columndata['class']='';
			$columndata['style']='';
			$columndata['align']='';
			$columndata['verticalalign']='';
			if(!($this->fetchcolumn($columndata)))
			{
				break;
			}
			$output=($output.(($columndata['header']) ? '<div style=padding: 0; height: 500px; width: 1000px;  overflow: scroll;border: 1px>'.'<th'.(strcmp($columndata['class'],'') ? ' class="'.$columndata['class'].'"' : '').(strcmp($columndata['style'],'') ? ' style="'.$columndata['style'].'"' : '').(strcmp($columndata['width'],'') ? ' width="'.$columndata['width'].'"' : '').(strcmp($columndata['backgroundcolor'],'') ? ' bgcolor="'.$columndata['backgroundcolor'].'"' : '').(strcmp($columndata['align'],'') ? ' align="'.$columndata['align'].'"' : '').(strcmp($columndata['verticalalign'],'') ? ' valign="'.$columndata['verticalalign'].'"' : '').'>'.$columndata['data']."</th>\n" : '<td'.(strcmp($columndata['class'],'') ? ' class="'.$columndata['class'].'"' : '').(strcmp($columndata['style'],'') ? ' style="'.$columndata['style'].'"' : '').(strcmp($columndata['width'],'') ? ' width="'.$columndata['width'].'"' : '').(strcmp($columndata['backgroundcolor'],'') ? ' bgcolor="'.$columndata['backgroundcolor'].'"' : '').(strcmp($columndata['align'],'') ? ' align="'.$columndata['align'].'"' : '').(strcmp($columndata['verticalalign'],'') ? ' valign="'.$columndata['verticalalign'].'"' : '').'>'.$columndata['data']."</td>\n"));
			$columndata['column']=($columndata['column']+1);
		}
		return $output;
	}
	
	Function fetchrow(&$rowdata)
	{
		return 0;
	}
	
	Function outputheader()
	{
		return (($this->headerlistingrowrange) ? $this->outputlistingrowrange() : '').(($this->headerlistingpages) ? $this->outputlistingpages() : '');
	}
	
	Function outputfooter()
	{
		return (($this->footerlistingpages) ? $this->outputlistingpages() : '').(($this->footerlistingrowrange) ? $this->outputlistingrowrange() : '');
	}
	
	Function outputrows()
	{
		$rowdata=array('row'=>0,'backgroundcolor'=>'');
		$output='';
		for(;;)
		{
			if(!($this->fetchrow($rowdata)))
			{
				break;
			}
			
			$output= ($output.'<tr'.(IsSet($rowdata['id']) ? ' id="'.$rowdata['id'].'"' : '').(strcmp($rowdata['backgroundcolor'],'') ? ' bgcolor="'.$rowdata['backgroundcolor'].'"' : '').(IsSet($rowdata['highlightcolor']) && strcmp($rowdata['highlightcolor'],'') && strcmp($rowdata['backgroundcolor'],'') && IsSet($rowdata['id']) ? ' onmouseover="if(document.layers) { document.layers[\''.(IsSet($rowdata['id']) ? $rowdata['id'] : '').'\'].bgColor=\''.$rowdata['highlightcolor'].'\' } else { if(document.all) { document.all[\''.(IsSet($rowdata['id']) ? $rowdata['id'] : '').'\'].style.background=\''.$rowdata['highlightcolor'].'\' } else { if(this.style) { this.style.background=\''.$rowdata['highlightcolor'].'\' } } }" onmouseout="if(document.layers) { document.layers[\''.(IsSet($rowdata['id']) ? $rowdata['id'] : '').'\'].bgColor=\''.(strcmp($rowdata['backgroundcolor'],'') ? $rowdata['backgroundcolor'] : '').'\' } else { if(document.all) { document.all[\''.(IsSet($rowdata['id']) ? $rowdata['id'] : '').'\'].style.background=\''.(strcmp($rowdata['backgroundcolor'],'') ? $rowdata['backgroundcolor'] : '').'\' } else { if(this.style) { this.style.background=\''.(strcmp($rowdata['backgroundcolor'],'') ? $rowdata['backgroundcolor'] : '').'\' } } }"' : '').">\n".$this->outputcolumns($rowdata['row'])."</tr>\n");
			
			$rowdata['row']=($rowdata['row']+1);
		
		}
		return $output;
	}
	
	Function outputtable()
	{
		return $this->outputheader().($this->center ? '<center>' : '').'<table'.(strcmp($this->class,'') ? ' class="'.$this->class.'"' : '').(strcmp($this->style,'') ? ' style="'.$this->style.'"' : '').(strcmp($this->width,'') ? ' width="'.$this->width.'"' : '').">\n".($this->border>0 ? "<tr>\n<td><center><table border=\"".strval($this->border)."\">\n" : '').$this->outputrows().($this->border>0 ? "</table></center></td>\n</tr>" : '')."\n</table>".($this->center ? '</center>' : '')."\n".$this->outputfooter();
	}
	
	Function pagerange($page)
	{
		$firstrow=($page*$this->rowsperpage);
		return (strval($firstrow+1).'-'.strval((($firstrow+$this->rowsperpage<$this->totalrows) ? $firstrow+$this->rowsperpage : $this->totalrows)));
	}
	
	Function pagelink($page,$data)
	{
		if(!strcmp($this->pagelinkvaluesstring,''))
		{
			Reset($this->pagelinkvalues);
			$end=(GetType($key=Key($this->pagelinkvalues))!='string');
			for(;!$end;)
			{
				$this->pagelinkvaluesstring=($this->pagelinkvaluesstring.'&'.$key.'='.$this->pagelinkvalues[$key]);
				Next($this->pagelinkvalues);
				$end=(GetType($key=Key($this->pagelinkvalues))!='string');
			}
		}
		return '<a href="'.((!strcmp($this->pagelinkurl,'')) ? $GLOBALS["PHP_SELF"] : $this->pagelinkurl).'?'.$this->pagevariable.'='.strval($page).$this->pagelinkvaluesstring.'">'.((!strcmp($data,'')) ? $this->pagerange($page) : $data).'</a>';
	}
	
	Function listingrowrange()
	{
		return ($this->pagerange($this->page).' / '.strval($this->totalrows));
	}
	
	Function outputlistingrowrange()
	{
		return (($this->totalrows>0) ? '<center><b>'.$this->listingrowrange()."</b></center>\n" : '');
	}
	
	Function listingpages()
	{
		$output='';
		$this->pagelinkvaluesstring='';
		if($this->page>0)
		{
			$link_page=($this->page-$this->listpages);
			if($link_page<0)
				$link_page=0;
			$output=($output.$this->pagelink(0,($this->encodeoutput($this->firstprefix).(($this->rangeinfirstlast) ? ' ('.$this->pagerange(0).')' : ''))).$this->rangelinkseparator);
			$link_page++;
			for(;$link_page<$this->page;)
			{
				$output=($output.$this->pagelink($link_page,((($link_page+1)==$this->page) ? ($this->encodeoutput($this->previousprefix).(($this->rangeinpreviousnext) ? ' ('.$this->pagerange($link_page).')' : '')) : '')).$this->rangelinkseparator);
				$link_page++;
			}
		}
		$output=($output.$this->pagerange($this->page));
		$maximum_page=(intval(($this->totalrows-1)/$this->rowsperpage));
		if($this->page<$maximum_page)
		{
			$link_page=($this->page+1);
			$last_page=($this->page+$this->listpages);
			if($last_page>$maximum_page)
				$last_page=$maximum_page;
			for(;$link_page<=($last_page-1);)
			{
				$output=($output.$this->rangelinkseparator.$this->pagelink($link_page,((($link_page-1)==$this->page) ? ((($this->rangeinpreviousnext) ? '('.$this->pagerange($link_page).') ' : '').$this->encodeoutput($this->nextsuffix)) : '')));
				$link_page++;
			}
			$output=($output.$this->rangelinkseparator.$this->pagelink($maximum_page,((($this->rangeinfirstlast) ? '('.$this->pagerange($maximum_page).') ' : '').$this->encodeoutput($this->lastsuffix))));
		}
		return $output;
	}
	
	Function outputlistingpages()
	{
		return (($this->totalrows>0) ? '<center><b>'.$this->listingpages()."</b></center>\n" : '');
	}
};

}
?>