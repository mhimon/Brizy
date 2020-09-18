def now = new Date()
def currentDate =  now.format("yyyy-MM-dd", TimeZone.getTimeZone('UTC'))
def buildTag = (!params.gitMerge)?"("+params.releaseBranch+")":"";
def zipFileName = "BuildFree-"+params.buildVersion+"-RC"+buildTag+".zip"

def today = new Date();
def releaseDate = today.format( 'yyyy-MM-dd' );

if(params.gitMerge) {
    zipFileName = "BuildFree-"+params.buildVersion+".zip"
}

env.BUILD_ZIP_PATH = params.brizySvnPath+"/"+zipFileName

pipeline {
    agent any
    environment {
            GITHUB_TOKEN     = credentials('git-token')
            SUBVERSION_TOKEN     = credentials('svn-secret')
    }
    stages {
        stage('Verifying requested version') {
            steps {
                 script {
                    if(folderExist(params.brizySvnPath+"/tags/"+params.buildVersion)) {
                        error("Build failed because this version is already built.")
                    }
                 }
            }
        }

        stage('Initialize SCM') {
            steps {
                sshagent (credentials: ['Git']) {
                     sh "./jenkins/git-initialize.sh '${params.releaseBranch}'"
                }
            }
        }

        stage('Prepare Composer') {
            steps {
                sh "./jenkins/composer.sh $GITHUB_TOKEN"
            }
        }

        stage('Prepare SVN and Create the Zip File') {
            steps {
                sh "./jenkins/prepare-svn.sh '${params.brizySvnPath}' '${zipFileName}'"
            }
        }

        stage('Prepare the build') {
            steps {
                writeFile file: 'changelog.txt', text: "\n= ${params.buildVersion} - ${releaseDate} =\n"+params.changelog+"\n"
                writeFile file: 'changelog.md', text: "\n### ${params.buildVersion} - ${releaseDate}\n"+params.changelog+"\n"
                sh "./jenkins/version-update.sh ${params.buildVersion} ${params.editorVersion} ${params.syncVersion} ./changelog.txt ./changelog.md"
                sh "rm -rf changelog.txt changelog.md"
            }
        }

        stage('Publish') {
            when {
                expression { return params.svnCommit }
            }
            steps {
                sh 'cd ' + params.brizySvnPath + ' && svn cp trunk tags/' + params.buildVersion
                sh "cd " + params.brizySvnPath + " && svn commit --non-interactive --trust-server-cert --username themefusecom --password '$SUBVERSION_TOKEN'  -m \"Version "+params.buildVersion+"\""
            }
        }

         stage('Git Merge') {
            when {
                expression { return params.gitMerge }
            }
            steps {
                sh 'git add ./public/editor-build/'+params.editorVersion
                sh 'git commit -a -m "Build '+params.buildVersion+'"'

                sshagent (credentials: ['Git']) {
                   sh 'git push origin '+params.releaseBranch
                }

                sh 'git checkout -t origin/master'
                sh 'git merge --no-ff -m "Merge ['+params.releaseBranch+'] in master" '+params.releaseBranch
                sh 'git tag '+params.buildVersion

                sshagent (credentials: ['Git']) {
                    sh 'git push origin master'
                }

                sh 'git checkout -t origin/develop'
                sh 'git merge --no-ff -m "Merge ['+params.releaseBranch+'] in develop" '+params.releaseBranch

                sshagent (credentials: ['Git']) {
                    sh 'git push origin master && git push origin develop && git push origin --tags && git push origin '+params.releaseBranch
                }
            }
        }
    }

     post {
         always {
             notifySlack(currentBuild.currentResult)
             cleanWs()
         }
     }
}


def notifySlack(String buildResult = 'STARTED', String zipPath = '') {

    def slackMessage= '';
    def color = '';

     if ( buildResult == "SUCCESS" ) {
       slackMessage = "Job: ${env.JOB_NAME} with build number ${env.BUILD_NUMBER} was successful.";
       color = '#00ff00';
     }
     else if( buildResult == "FAILURE" ) {
       slackMessage = "Job: ${env.JOB_NAME} with build number ${env.BUILD_NUMBER} was failed.";
       color = '#ff0000';
     }
     else if( buildResult == "UNSTABLE" ) {
        slackMessage = "Job: ${env.JOB_NAME} with build number ${env.BUILD_NUMBER} was unstable.";
        color = '#ff8800';
     }
     else {
        slackMessage = "Job: ${env.JOB_NAME} with build number ${env.BUILD_NUMBER} its result was unclear.";
        color = '#ff8800';
     }

    def changelog = params.changelog.replaceAll('"','\"');

    def slackMessageJson = """
    {
        "attachments": [
            {
                "color": "${color}",
                "title": "${slackMessage}",
                "fields": [
                    {
                        "title": "Plugin version",
                        "value": "${params.buildVersion}",
                        "short": true
                    },
                    {
                        "title": "Editor version",
                        "value": "${params.editorVersion}",
                        "short": true
                    },
                    {
                        "title": "Branch",
                        "value": "${params.releaseBranch}",
                        "short": true
                    },
                    {
                        "title": "Changelog",
                        "value": "${changelog}",
                        "short": false
                    }
                ],
                "footer": "Brizy",
                "footer_icon": "https://brizy.io/wp-content/uploads/2018/02/logo-symbol.png"
            }
        ]
    }
    """;
    sendSlackMessage(slackMessageJson);

     if ( buildResult == "SUCCESS" ) {
       withCredentials([string(credentialsId: 'slack', variable: 'SECRET')]) {
            sh '''
                set +x
                curl -F file=@$BUILD_ZIP_PATH -F channels=#jenkins -F token="$SECRET" https://slack.com/api/files.upload
            '''
       }
     }



}

def folderExist(path){
    return sh(script: "test -d ${path} && echo 1 || echo 0", returnStdout: true).trim()=="1"
}

def sendSlackMessage(String message) {
    message = message.replaceAll("'","\'").replaceAll("\n",'\n');
    withCredentials([string(credentialsId: 'slack-hook-url', variable: 'hook_urk')]) {
      sh """
        curl -X POST -H 'Content-type: application/json' --data '${message}' ${hook_urk}
      """
    }
}
