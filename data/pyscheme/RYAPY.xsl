<?xml version="1.0" encoding="utf-8"?>
<xsl:stylesheet version="1.0" xmlns:xsl="http://www.w3.org/1999/XSL/Transform">
  <xsl:template match="/">
    <html>
      <head>
        <title>RYA PY input document</title>
        <style type="text/css">
          #main {width: 1000px; margin: 0 auto 0 auto; background-color: #ffffff; padding: 5px; border: 2px solid #999999;}
          h1 {text-align: center;}
          table {width: 100%; font-size: 80%; border-collapse: collapse; margin: 10px 0 0 0;}
          td, th {border: 1px solid #999999;}
        </style>
      </head>
      <body>
        <div id="main">
          <h1>RYA PY Document</h1>
          <xsl:apply-templates select="RYAPY/event"/>
          <xsl:apply-templates select="RYAPY/admin"/>
          <xsl:apply-templates select="RYAPY/races"/>
        </div>
      </body>
    </html>
  </xsl:template>

  <xsl:template name="event" match="RYAPY/event">
    <div style="float: left;">
      Club:
    </div>
    <div style="float: left;">
      <xsl:value-of select="clubname"/>
    </div>
    <br style="clear:both;" />
    <div style="float: left;">
      Club ID:
    </div>
    <div style="float: right;">
      <xsl:value-of select="clubid"/>
    </div>
    <br style="clear:both;" />
    <div style="float: left;">
      Event name:
    </div>
    <div style="float: right;">
      <xsl:value-of select="eventname"/>
    </div>
    <br style="clear:both;" />
    <div style="float: left;">
      Event ID:
    </div>
    <div style="float: right;">
      <xsl:value-of select="eventid"/>
    </div>
    <br style="clear:both;" />
  </xsl:template>
  <xsl:template name="admin" match="RYAPY/admin" >
    <div style="float: left;">
      Submitted:
    </div>
    <div style="float: right;">
      <xsl:value-of select="submittedon"/> at <xsl:value-of select="submittedat"/>
    </div>
    <br style="clear:both;" />
    <div style="float: left;">
      Using:
    </div>
    <div style="float: right;">
      <xsl:value-of select="source"/> version <xsl:value-of select="sourcever"/>
    </div>
    <br style="clear:both;" />
  </xsl:template>
  <xsl:template name="races" match="RYAPY/races">
    <xsl:for-each select="race">
      <hr/>
      <div style="float: left;">
        Race:
      </div>
      <div style="float: right;">
        <xsl:value-of select="date"/> race number <xsl:value-of select="raceno"/>
      </div>
      <br style="clear:both;" />
      <xsl:for-each select="starts/start">
        <div style="float: left;">
          Start:
        </div>
        <div style="float: right;">
          <xsl:value-of select="name"/> at <xsl:value-of select="starttime"/>
        </div>
        <br style="clear:both;" />
        <div style="float: left;">
          Wind:
        </div>
        <div style="float: right;">
          <xsl:value-of select="winddir"/> degrees / <xsl:value-of select="windspeed"/>
        </div>
        <br style="clear:both;" />
        <table>
          <tr>
            <th>Rank</th>
            <th>Sail number</th>
            <th>Class/type</th>
            <th>Category</th>
            <th>Persons</th>
            <th>Rig</th>
            <th>Spinnaker</th>
            <th>Keel</th>
            <th>Engine</th>
            <th>Helm</th>
            <th>Crew1</th>
            <th>Crew2</th>
            <th>Rating</th>
            <th>Elapsed</th>
            <th>Corrected</th>
            <th>Laps</th>
          </tr>
          <xsl:for-each select="entries/entry">
            <tr>
              <td>
                <xsl:value-of select="rank"/>
              </td>
              <td>
                <xsl:value-of select="sailno"/>
              </td>
              <td>
                <xsl:value-of select="classid"/>
              </td>
              <td>
                <xsl:value-of select="category"/>
              </td>
              <td>
                <xsl:value-of select="persons"/>
              </td>
              <td>
                <xsl:value-of select="rig"/>
              </td>
              <td>
                <xsl:value-of select="spinnaker"/>
              </td>
              <td>
                <xsl:value-of select="keel"/>
              </td>
              <td>
                <xsl:value-of select="engine"/>
              </td>
              <td>
                <xsl:value-of select="helm"/>
              </td>
              <td>
                <xsl:value-of select="crew1"/>
              </td>
              <td>
                <xsl:value-of select="crew2"/>
              </td>
              <td>
                <xsl:value-of select="rating"/>
              </td>
              <td>
                <xsl:value-of select="elapsed"/>
              </td>
              <td>
                <xsl:value-of select="corrected"/>
              </td>
              <td>
                <xsl:value-of select="laps"/>
              </td>
            </tr>
          </xsl:for-each>
        </table>
      </xsl:for-each>
    </xsl:for-each>
  </xsl:template>
</xsl:stylesheet>
